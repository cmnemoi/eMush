<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiseaseEventSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

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

        $eventIsHealingAction = $event->getReason() === ActionEnum::SELF_HEAL
            || $event->getReason() === ActionEnum::HEAL;

        $reason = $event->getReason();

        if ($eventIsHealingAction) {
            $key = LogEnum::DISEASE_CURED_PLAYER;
            $event->setVisibility(VisibilityEnum::PUBLIC);
        } elseif ($reason === ActionEnum::CONSUME) {
            $key = LogEnum::DISEASE_CURED_DRUG;
            $event->setVisibility(VisibilityEnum::PUBLIC);
        } elseif (in_array($reason, LogEnum::getSurgeryLogs())) {
            $key = $reason;
        } else {
            $key = LogEnum::DISEASE_CURED;
        }

        $this->createEventLog($key, $event, $player);
    }

    public function onDiseaseTreated(DiseaseEvent $event)
    {
        $player = $event->getPlayerDisease()->getPlayer();

        if ($event->getReason() === ActionTypeEnum::ACTION_HEAL) {
            $key = LogEnum::DISEASE_TREATED_PLAYER;
            $event->setVisibility(VisibilityEnum::PUBLIC);
        } elseif ($event->getReason() === ActionEnum::CONSUME) {
            $key = LogEnum::DISEASE_TREATED_DRUG;
            $event->setVisibility(VisibilityEnum::PUBLIC);
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
