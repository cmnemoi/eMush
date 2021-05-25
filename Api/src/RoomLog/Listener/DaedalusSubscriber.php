<?php

namespace Mush\RoomLog\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
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
            DaedalusEvent::NEW_DAEDALUS => 'onNewDaedalus',
        ];
    }

    public function onNewDaedalus(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $garden = $daedalus->getPlaceByName(RoomEnum::HYDROPONIC_GARDEN);
        if ($garden) {
            $this->roomLogService->createLog(LogEnum::GARDEN_DESTROYED, $garden, VisibilityEnum::PUBLIC, 'event_log');
        }
    }
}
