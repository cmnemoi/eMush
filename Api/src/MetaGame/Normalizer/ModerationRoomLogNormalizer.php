<?php

declare(strict_types=1);

namespace Mush\MetaGame\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
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

    public function getSupportedTypes(?string $format): array
    {
        return [
            RoomLog::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var RoomLog $roomLog */
        $roomLog = $object;
        $language = $roomLog->getDaedalusInfo()->getLanguage();

        $logParameters = $roomLog->getParameters();
        $logParameters['is_tracker'] = 'false';

        return [
            'log' => "{$this->translationService->translate(
                $roomLog->getLog(),
                $logParameters,
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
            'canBeHidden' => $this->canBeHidden($roomLog),
        ];
    }

    private function canBeHidden(RoomLog $roomLog): bool
    {
        return match (true) {
            $roomLog->getType() === 'triumph' => true,
            \in_array($roomLog->getLog(), PlayerModifierLogEnum::PLAYER_VARIABLE_LOGS['gain'], true) => true,
            \in_array($roomLog->getLog(), PlayerModifierLogEnum::PLAYER_VARIABLE_LOGS['loss'], true) => true,
            \in_array($roomLog->getLog(), StatusEventLogEnum::CHARGE_STATUS_UPDATED_LOGS['gain']['value'], true) => true,
            $roomLog->getLog() === 'daily_morale_loss' => true,
            $roomLog->getLog() === 'logistic_log' => true,
            $roomLog->getLog() === 'antisocial_morale_loss' => true,
            $roomLog->getLog() === 'disease_cured' => true,
            $roomLog->getLog() === 'disorder_appear' => true,
            $roomLog->getLog() === 'trauma_disease' => true,
            $roomLog->getLog() === 'disease_appear' => true,
            $roomLog->getLog() === 'injury_appear' => true,
            default => false
        };
    }
}
