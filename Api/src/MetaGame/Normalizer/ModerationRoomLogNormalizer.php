<?php

declare(strict_types=1);

namespace Mush\MetaGame\Normalizer;

use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class ModerationRoomLogNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslationServiceInterface $translationService,
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof RoomLog && \in_array('moderation_read', $context['groups'] ?? [], true);
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var RoomLog $roomLog */
        $roomLog = $object;
        $language = LanguageEnum::FRENCH;

        return [
            'log' => "{$this->translationService->translate(
                $roomLog->getLog(),
                $roomLog->getParameters(),
                $roomLog->getType(),
                $language
            )} ({$roomLog->getLog()})",
            'visibility' => $roomLog->getVisibility(),
            'date' => $roomLog->getCreatedAt()?->format('d/m/Y H:i'),
            'place' => "{$this->translationService->translate(
                $roomLog->getPlace() . '.name',
                [],
                'rooms',
                $language
            )} ({$roomLog->getPlace()})",
            'day' => $roomLog->getDay(),
            'cycle' => $roomLog->getCycle(),
        ];
    }
}
