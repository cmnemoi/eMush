<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiseaseEventSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    private const array CURE_LOG_MAP = [
        ActionEnum::HEAL => LogEnum::DISEASE_CURED_PLAYER,
        ActionEnum::SELF_HEAL => LogEnum::DISEASE_CURED_PLAYER,
        ActionTypeEnum::ACTION_HEAL => LogEnum::DISEASE_CURED_PLAYER,
        ActionEnum::CONSUME => LogEnum::DISEASE_CURED_DRUG,
        LogEnum::SURGERY_CRITICAL_SUCCESS => LogEnum::SURGERY_CRITICAL_SUCCESS,
        LogEnum::SURGERY_SUCCESS => LogEnum::SURGERY_SUCCESS,
        LogEnum::SELF_SURGERY_CRITICAL_SUCCESS => LogEnum::SELF_SURGERY_CRITICAL_SUCCESS,
        LogEnum::SELF_SURGERY_SUCCESS => LogEnum::SELF_SURGERY_SUCCESS,
        DiseaseCauseEnum::OVERRODE => LogEnum::DISEASE_OVERRIDDEN,
        DiseaseStatusEnum::DRUG_HEALED => LogEnum::DISEASE_CURED_DRUG,
    ];

    private const array TREAT_LOG_MAP = [
        ActionEnum::HEAL => LogEnum::DISEASE_TREATED_PLAYER,
        ActionEnum::SELF_HEAL => LogEnum::DISEASE_TREATED_PLAYER,
        ActionTypeEnum::ACTION_HEAL => LogEnum::DISEASE_TREATED_PLAYER,
        ActionEnum::CONSUME => LogEnum::DISEASE_TREATED_DRUG,
        ActionEnum::CONSUME_DRUG => LogEnum::DISEASE_TREATED_DRUG,
    ];

    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DiseaseEvent::CURE_DISEASE => 'onDiseaseCure',
            DiseaseEvent::TREAT_DISEASE => 'onDiseaseTreated',
            DiseaseEvent::APPEAR_DISEASE => 'onDiseaseAppear',
        ];
    }

    public function onDiseaseCure(DiseaseEvent $event): void
    {
        $player = $event->getTargetPlayer();

        $key = $event->mapLog(self::CURE_LOG_MAP);
        if ($key === null) {
            $key = LogEnum::DISEASE_CURED;
        }

        $this->createEventLog($key, $event, $player);
    }

    public function onDiseaseTreated(DiseaseEvent $event): void
    {
        $player = $event->getTargetPlayer();

        $key = $event->mapLog(self::TREAT_LOG_MAP);
        if ($key === null) {
            $key = LogEnum::DISEASE_TREATED;
        }

        $event->setVisibility(VisibilityEnum::PUBLIC);
        $this->createEventLog($key, $event, $player);
    }

    public function onDiseaseAppear(DiseaseEvent $event): void
    {
        $player = $event->getTargetPlayer();
        $diseaseConfig = $event->getDiseaseConfig();
        $key = match ($diseaseConfig->getType()) {
            MedicalConditionTypeEnum::DISEASE => LogEnum::DISEASE_APPEAR,
            MedicalConditionTypeEnum::DISORDER => LogEnum::DISORDER_APPEAR,
            MedicalConditionTypeEnum::INJURY => LogEnum::INJURY_APPEAR,
            default => $diseaseConfig->getType()
        };

        $this->createEventLog($key, $event, $player);
    }

    private function createEventLog(string $logKey, DiseaseEvent $event, Player $player): void
    {
        $this->roomLogService->createLog(
            $logKey,
            $event->getPlace(),
            $event->getVisibility(),
            'event_log',
            $player,
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
