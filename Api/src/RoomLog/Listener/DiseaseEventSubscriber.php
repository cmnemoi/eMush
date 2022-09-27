<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiseaseEventSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    private const CURE_LOG_MAP = [
        ActionEnum::HEAL => LogEnum::DISEASE_CURED_PLAYER,
        ActionEnum::SELF_HEAL => LogEnum::DISEASE_CURED_PLAYER,
        ActionTypeEnum::ACTION_HEAL => LogEnum::DISEASE_CURED_PLAYER,
        ActionEnum::CONSUME => LogEnum::DISEASE_CURED_DRUG,
        LogEnum::SURGERY_CRITICAL_SUCCESS => LogEnum::SURGERY_CRITICAL_SUCCESS,
        LogEnum::SURGERY_SUCCESS => LogEnum::SURGERY_SUCCESS,
        LogEnum::SELF_SURGERY_CRITICAL_SUCCESS => LogEnum::SELF_SURGERY_CRITICAL_SUCCESS,
        LogEnum::SELF_SURGERY_SUCCESS => LogEnum::SELF_SURGERY_SUCCESS,
        DiseaseCauseEnum::OVERRODE => LogEnum::DISEASE_OVERRIDDEN,
    ];

    private const TREAT_LOG_MAP = [
        ActionEnum::HEAL => LogEnum::DISEASE_TREATED_PLAYER,
        ActionEnum::SELF_HEAL => LogEnum::DISEASE_TREATED_PLAYER,
        ActionTypeEnum::ACTION_HEAL => LogEnum::DISEASE_TREATED_PLAYER,
        ActionEnum::CONSUME => LogEnum::DISEASE_TREATED_DRUG,
    ];

    public function __construct(
        RoomLogServiceInterface $roomLogService,
    ) {
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

    public function onDiseaseCure(DiseaseEvent $event)
    {
        $player = $event->getPlayerDisease()->getPlayer();

        $reason = $event->getReason();

        if (key_exists($reason, self::CURE_LOG_MAP)) {
            $key = self::CURE_LOG_MAP[$reason];
        } else {
            $key = LogEnum::DISEASE_CURED;
        }

        $this->createEventLog($key, $event, $player);
    }

    public function onDiseaseTreated(DiseaseEvent $event)
    {
        $player = $event->getPlayerDisease()->getPlayer();

        $reason = $event->getReason();

        if (key_exists($reason, self::TREAT_LOG_MAP)) {
            $key = self::TREAT_LOG_MAP[$reason];
        } else {
            $key = LogEnum::DISEASE_TREATED;
        }

        $this->createEventLog($key, $event, $player);
    }

    public function onDiseaseAppear(DiseaseEvent $event)
    {
        $player = $event->getPlayer();
        $diseaseConfig = $event->getDiseaseConfig();
        $key = match ($diseaseConfig->getType()) {
            TypeEnum::DISEASE => LogEnum::DISEASE_APPEAR,
            TypeEnum::DISORDER => LogEnum::DISORDER_APPEAR,
            TypeEnum::INJURY => LogEnum::INJURY_APPEAR,
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
