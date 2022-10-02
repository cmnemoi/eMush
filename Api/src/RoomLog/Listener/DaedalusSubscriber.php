<?php

namespace Mush\RoomLog\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
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
}
