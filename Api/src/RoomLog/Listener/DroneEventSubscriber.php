<?php

declare(strict_types=1);

namespace Mush\RoomLog\Listener;

use Mush\Equipment\Event\AbstractDroneEvent;
use Mush\Equipment\Event\DroneExtinguishedFireEvent;
use Mush\Equipment\Event\DroneHitHunterEvent;
use Mush\Equipment\Event\DroneKillHunterEvent;
use Mush\Equipment\Event\DroneLandedEvent;
use Mush\Equipment\Event\DroneMovedEvent;
use Mush\Equipment\Event\DroneRepairedEvent;
use Mush\Equipment\Event\DroneTakeoffEvent;
use Mush\Equipment\Event\DroneTurboWorkedEvent;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DroneEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RoomLogServiceInterface $roomLogService,
        private TranslationServiceInterface $translationService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DroneExtinguishedFireEvent::class => 'onDroneExtinguishedFire',
            DroneHitHunterEvent::class => 'onDroneHitHunter',
            DroneKillHunterEvent::class => 'onDroneKillHunter',
            DroneLandedEvent::class => 'onDroneLanded',
            DroneMovedEvent::class => 'onDroneMoved',
            DroneRepairedEvent::class => 'onDroneRepaired',
            DroneTakeoffEvent::class => 'onDroneTakeoff',
            DroneTurboWorkedEvent::class => 'onDroneTurboWorked',
        ];
    }

    public function onDroneExtinguishedFire(DroneExtinguishedFireEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DRONE_EXTINGUISHED_FIRE,
            $event->getDrone()->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    public function onDroneHitHunter(DroneHitHunterEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DRONE_HIT_HUNTER,
            $event->getDrone()->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    public function onDroneKillHunter(DroneKillHunterEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DRONE_KILL_HUNTER,
            $event->getDrone()->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    public function onDroneLanded(DroneLandedEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DRONE_LAND,
            $event->getDrone()->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    public function onDroneMoved(DroneMovedEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DRONE_EXITED_ROOM,
            $event->getOldRoom(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );

        $this->roomLogService->createLog(
            LogEnum::DRONE_ENTERED_ROOM,
            $event->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    public function onDroneRepaired(DroneRepairedEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DRONE_REPAIRED_EQUIPMENT,
            $event->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    public function onDroneTakeoff(DroneTakeoffEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DRONE_TAKEOFF,
            $event->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    public function onDroneTurboWorked(DroneTurboWorkedEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DRONE_TURBO_WORKED,
            $event->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    private function getLogParameters(AbstractDroneEvent $event): array
    {
        $logParameters = $event->getLogParameters();
        $logParameters['drone'] = $this->translationService->translate(
            key: 'drone',
            parameters: $event->getLogParameters(),
            domain: 'event_log',
            language: $event->getDaedalusLanguage()
        );

        return $logParameters;
    }
}
