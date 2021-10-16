<?php

namespace Mush\RoomLog\Listener;

use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
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
            DiseaseEvent::APPEAR_DISEASE => 'onDiseaseAppear',
        ];
    }

    public function onDiseaseCure(DiseaseEvent $event)
    {
        $player = $event->getPlayerDisease()->getPlayer();

        $this->roomLogService->createLog(
            LogEnum::DISEASE_CURED,
            $event->getPlace(),
            VisibilityEnum::PRIVATE,
            'event_log',
            $player,
            $event->getLogParameters(),
            $event->getTime()
        );
    }

    public function onDiseaseAppear(DiseaseEvent $event)
    {
        $player = $event->getPlayer();
        $diseaseConfig = $event->getDiseaseConfig();
        $log = match ($diseaseConfig->getType()) {
            TypeEnum::DISEASE => LogEnum::DISEASE_APPEAR,
            TypeEnum::DISORDER => LogEnum::DISORDER_APPEAR,
            default => $diseaseConfig->getType()
        };

        $this->roomLogService->createLog(
            $log,
            $event->getPlace(),
            VisibilityEnum::PRIVATE,
            'event_log',
            $player,
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
