<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        RoomLogServiceInterface $roomLogService
    ) {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::START_DAEDALUS => 'onDaedalusStart',
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onDaedalusStart(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $garden = $daedalus->getPlaceByName(RoomEnum::HYDROPONIC_GARDEN);
        if ($garden) {
            $this->roomLogService->createLog(LogEnum::GARDEN_DESTROYED, $garden, VisibilityEnum::PUBLIC, 'event_log');
        }
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        if ($event->hasTag(ActionEnum::LEAVE_ORBIT)) {
            $this->hideOldPlanetExplorationLogs($event->getDaedalus());
        }
    }

    private function hideOldPlanetExplorationLogs(Daedalus $daedalus): void
    {
        $planetLogs = $this->roomLogService->findAllByDaedalusAndPlace($daedalus, $daedalus->getPlanetPlace());

        $planetLogs->map(static fn (RoomLog $log) => $log->setVisibility(VisibilityEnum::HIDDEN));
        $planetLogs->map(fn (RoomLog $log) => $this->roomLogService->persist($log));
    }
}
