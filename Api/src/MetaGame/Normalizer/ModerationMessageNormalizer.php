<?php

declare(strict_types=1);

namespace Mush\MetaGame\Normalizer;

use Mush\Chat\Entity\Message;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class ModerationMessageNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Message && \in_array('moderation_read', $context['groups'] ?? [], true) && $data->getParent() === null;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Message::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Message $message */
        $message = $object;
        $messageChildren = [];
        $language = $message->getChannel()->getDaedalusInfo()->getLanguage();

        /** @var Message $child */
        foreach ($message->getChild() as $child) {
            $messageChildren[] = $this->normalize($child, $format, $context);
        }

        $character = null;
        if ($message->getNeron()) {
            $character = CharacterEnum::NERON;
        } elseif ($message->getAuthor()) {
            $character = $message->getAuthor()?->getCharacterConfig()->getName();
        }

        $translationParameters = $message->getTranslationParameters();
        $translatedAuthor = null;
        if ($message->getAuthor()) {
            $translatedAuthor = $this->translationService->translate(
                $message->getAuthor()?->getCharacterConfig()->getName(),
                [],
                'characters',
                $language
            );
            $translatedMessage = $message->getMessage();
        } if ($message->isNeronMessage()) {
            $translatedMessage = $this->translationService->translate(
                $message->getMessage(),
                $translationParameters,
                'neron',
                $language
            );
        } else {
            $translatedMessage = $this->translationService->translate(
                $message->getMessage(),
                $translationParameters,
                'event_log',
                $language
            );
        }

        $translatedPirateAuthor = null;
        if ($message->getPirateAuthor()) {
            $translatedPirateAuthor = $this->translationService->translate(
                $message->getPirateAuthor()?->getCharacterConfig()->getName(),
                [],
                'characters',
                $language
            );
        }

        return [
            'id' => $message->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate(
                    "{$character}.name",
                    [],
                    'characters',
                    $language
                ) . $this->getMessageAuthor($message, $translatedAuthor, $translatedPirateAuthor),
            ],
            'message' => $translatedMessage !== $message->getMessage() ? $translatedMessage . " ({$message->getMessage()})" : $translatedMessage,
            'date' => $message->getCreatedAt()?->format('d/m/Y H:i'),
            'child' => $messageChildren,
        ];
    }

    private function getMessageAuthor(Message $message, ?string $translatedAuthor, ?string $translatedPirateAuthor): string
    {
        if ($translatedPirateAuthor) {
            return " ({$translatedPirateAuthor})";
        }
        if ($message->getNeron() && $translatedAuthor) {
            return " ({$translatedAuthor})";
        }

        return '';
    }
}
