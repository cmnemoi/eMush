<?php

declare(strict_types=1);

namespace Mush\RoomLog\Listener;

use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlanetSectorEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private RoomLogServiceInterface $roomLogService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlanetSectorEvent::PLANET_SECTOR_EVENT => ['onPlanetSectorEvent', EventPriorityEnum::HIGH],
        ];
    }

    public function onPlanetSectorEvent(PlanetSectorEvent $event): void
    {
        if ($event->isNegative() === false) {
            return;
        }

        $exploration = $event->getExploration();
        foreach ($exploration->getTraitors() as $traitor) {
            $this->roomLogService->createLog(
                logKey: LogEnum::TRAITOR_WORKED,
                place: $traitor->getPlace(),
                visibility: VisibilityEnum::PRIVATE,
                player: $traitor,
                type: 'event_log',
            );
        }
    }
}
