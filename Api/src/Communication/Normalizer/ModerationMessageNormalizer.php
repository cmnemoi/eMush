<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Message;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ModerationMessageNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Message && in_array('moderation_read', $context['groups'] ?? []);
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Message $message */
        $message = $object;
        $messageChildren = [];
        $language = $message->getChannel()->getDaedalusInfo()->getLanguage();

        /** @var Message $child */
        foreach ($message->getChild() as $child) {
            $messageChildren[] = $this->normalize($child, $format, $context);
        }

        if ($message->getAuthor()) {
            $character = $message->getAuthor()?->getCharacterConfig()->getName();
        } else {
            $character = null;
            if ($message->getNeron()) {
                $character = CharacterEnum::NERON;
            }
        }

        $translationParameters = $message->getTranslationParameters();
        if ($message->getAuthor()) {
            $messageContent = $message->getMessage();
        } elseif ($message->getNeron()) {
            $messageContent = $this->translationService->translate(
                $message->getMessage(),
                $translationParameters,
                'neron',
                $language
            );
        } else {
            $messageContent = $this->translationService->translate(
                $message->getMessage(),
                $translationParameters,
                'event_log',
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
                ),
            ],
            'message' => $messageContent,
            'date' => $message->getCreatedAt()?->format('d/m/Y H:i'),
            'child' => $messageChildren,
        ];
    }
}
