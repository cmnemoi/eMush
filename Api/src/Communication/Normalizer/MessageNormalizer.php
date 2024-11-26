<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Message;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\Random\RandomStringInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MessageNormalizer implements NormalizerInterface
{
    public function __construct(
        private RandomStringInterface $randomString,
        private TranslationServiceInterface $translationService
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Message && !\in_array('moderation_read', $context['groups'] ?? [], true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Message::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $currentPlayer = $this->currentPlayer($context);
        $message = $this->message($object);
        $language = $currentPlayer->getLanguage();

        $childMessages = $message->getChild()->map(fn (Message $child) => $this->normalize($child, $format, $context))->toArray();

        return [
            'id' => $message->getId(),
            'character' => $this->translatedMessageAuthor($message, $currentPlayer),
            'message' => $this->translatedMessageText($message, $currentPlayer),
            'date' => $this->translatedMessageDate($message->getCreatedAtOrThrow(), $language),
            'child' => $childMessages,
            'isUnread' => $message->isUnreadBy($currentPlayer),
        ];
    }

    private function translatedMessageText(Message $message, Player $currentPlayer): string
    {
        $language = $currentPlayer->getLanguage();
        $translationParameters = $message->getTranslationParameters();

        if ($currentPlayer->isHuman() && $message->isInMushChannel()) {
            $translationParameters = $this->scrambleCharacterNames($translationParameters);
        }

        if ($message->getAuthor()) {
            return $message->getMessage();
        }
        if ($message->getNeron()) {
            return $this->translationService->translate(
                $message->getMessage(),
                $translationParameters,
                'neron',
                $language
            );
        }

        return $this->translationService->translate(
            $message->getMessage(),
            $translationParameters,
            'event_log',
            $language
        );
    }

    private function translatedMessageAuthor(Message $message, Player $currentPlayer): array
    {
        $author = $this->messageAuthor($message, $currentPlayer);
        if (!$author) {
            return [
                'key' => null,
                'value' => null,
            ];
        }

        return [
            'key' => $author,
            'value' => $this->translationService->translate(
                key: "{$author}.name",
                parameters: [],
                domain: 'characters',
                language: $currentPlayer->getLanguage()
            ),
        ];
    }

    private function messageAuthor(Message $message, Player $currentPlayer): ?string
    {
        if ($message->getNeron()) {
            return CharacterEnum::NERON;
        }
        if ($message->getAuthor()) {
            return $currentPlayer->isHuman() && $message->isInMushChannel() ? CharacterEnum::MUSH : $message->getAuthorAsPlayerOrThrow()->getLogName();
        }

        return null;
    }

    private function translatedMessageDate(\DateTime $dateTime, string $language): string
    {
        $dateInterval = $dateTime->diff(new \DateTime());

        $days = (int) $dateInterval->format('%a');
        $hours = (int) $dateInterval->format('%H');
        $minutes = (int) $dateInterval->format('%i');

        if ($days > 0) {
            return $this->translationService->translate('message_date.more_day', ['quantity' => $days], 'chat', $language);
        }
        if ($hours > 0) {
            return $this->translationService->translate('message_date.more_hour', ['quantity' => $hours], 'chat', $language);
        }
        if ($minutes > 0) {
            return $this->translationService->translate('message_date.more_minute', ['quantity' => $minutes], 'chat', $language);
        }

        return $this->translationService->translate('message_date.less_minute', [], 'chat', $language);
    }

    private function scrambleCharacterNames(array $translationParameters): array
    {
        if (\array_key_exists('character', $translationParameters)) {
            $translationParameters['character'] = $this->getRubbishName($translationParameters['character']);
        }
        if (\array_key_exists('target_character', $translationParameters)) {
            $translationParameters['target_character'] = $this->getRubbishName($translationParameters['target_character']);
        }

        return $translationParameters;
    }

    private function getRubbishName(string $originalName): string
    {
        do {
            $scrambled = $this->randomString->generate(minLength: 3, maxLength: 8);
        } while ($scrambled === $originalName);

        return $scrambled;
    }

    private function message(mixed $object): Message
    {
        return $object instanceof Message ? $object : throw new \RuntimeException('MessageNormalizer only accepts Message objects');
    }

    private function currentPlayer(array $context): Player
    {
        return $context['currentPlayer'] ?? throw new \RuntimeException('MessageNormalizer requires a currentPlayer in the context');
    }
}
