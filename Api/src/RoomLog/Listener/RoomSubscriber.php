<?php

namespace Mush\RoomLog\Listener;

use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
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
            RoomEvent::TREMOR => 'onTremor',
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
        ];
    }

    public function onTremor(RoomEvent $event): void
    {
        $room = $event->getRoom();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        if ($event->isGravity()) {
            $logKey = LogEnum::TREMOR_GRAVITY;
        } else {
            $logKey = LogEnum::TREMOR_NO_GRAVITY;
        }

        $this->roomLogService->createLog(
            $logKey,
            $room,
            VisibilityEnum::PUBLIC,
            'event_log',
            null,
            [],
            $event->getTime()
        );
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getRoom();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $this->roomLogService->createLog(
            LogEnum::ELECTRIC_ARC,
            $room,
            VisibilityEnum::PUBLIC,
            'event_log',
            null,
            [],
            $event->getTime()
        );
    }
}
