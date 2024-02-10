<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Message;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MessageNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Message;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $child = [];

        // @HACK: If we normalize messages with API Platform, we don't have a current player in the context
        // so doing this ugly if else.
        // @TODO: Find a way to use API Platform normalization_context to handle this
        if (array_key_exists('currentPlayer', $context)) {
            /** @var Player $currentPlayer */
            $currentPlayer = $context['currentPlayer'];
            $language = $currentPlayer->getDaedalus()->getLanguage();
        } else {
            $language = LanguageEnum::FRENCH;
        }

        /** @var Message $children */
        foreach ($object->getChild() as $children) {
            $child[] = $this->normalize($children, $format, $context);
        }

        if ($object->getAuthor()) {
            $character = $object->getAuthor()->getCharacterConfig()->getName();
        } else {
            $character = null;
            if ($object->getNeron()) {
                $character = CharacterEnum::NERON;
            }
        }

        $translationParameters = $object->getTranslationParameters();
        if ($object->getAuthor()) {
            $message = $object->getMessage();
        } elseif ($object->getNeron()) {
            $message = $this->translationService->translate(
                $object->getMessage(),
                $translationParameters,
                'neron',
                $language
            );
        } else {
            $message = $this->translationService->translate(
                $object->getMessage(),
                $translationParameters,
                'event_log',
                $language
            );
        }

        return [
            'id' => $object->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate(
                    "{$character}.name",
                    [],
                    'characters',
                    $language
                ),
            ],
            'message' => $message,
            'date' => $this->getMessageDate($object->getCreatedAt(), $language),
            'child' => $child,
        ];
    }

    private function getMessageDate(\DateTime $dateTime, string $language): string
    {
        $dateInterval = $dateTime->diff(new \DateTime());

        $days = intval($dateInterval->format('%a'));
        $hours = intval($dateInterval->format('%H'));
        $minutes = intval($dateInterval->format('%i'));

        if ($days > 0) {
            return $this->translationService->translate('message_date.more_day', ['quantity' => $days], 'chat', $language);
        } elseif ($hours > 0) {
            return $this->translationService->translate('message_date.more_hour', ['quantity' => $hours], 'chat', $language);
        } elseif ($minutes > 0) {
            return $this->translationService->translate('message_date.more_minute', ['quantity' => $minutes], 'chat', $language);
        } else {
            return $this->translationService->translate('message_date.less_minute', [], 'chat', $language);
        }
    }
}
