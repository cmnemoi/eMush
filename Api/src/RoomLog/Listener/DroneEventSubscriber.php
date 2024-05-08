<?php

declare(strict_types=1);

namespace Mush\RoomLog\Listener;

use Mush\Equipment\Event\AbstractDroneEvent;
use Mush\Equipment\Event\DroneMovedEvent;
use Mush\Equipment\Event\DroneRepairedEvent;
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
            DroneMovedEvent::class => 'onDroneMoved',
            DroneRepairedEvent::class => 'onDroneRepaired',
        ];
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
