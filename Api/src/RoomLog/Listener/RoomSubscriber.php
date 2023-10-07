<?php

namespace Mush\RoomLog\Listener;

use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
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
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $daedalus = $room->getDaedalus();
        if (
            $daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY)
            || $daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY_REPAIRED)
        ) {
            $logKey = LogEnum::TREMOR_NO_GRAVITY;
        } else {
            $logKey = LogEnum::TREMOR_GRAVITY;
        }

        $this->roomLogService->createLog(
            $logKey,
            $room,
            $event->getVisibility(),
            'event_log',
            null,
            $event->getLogParameters(),
            $event->getTime()
        );
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $this->roomLogService->createLog(
            LogEnum::ELECTRIC_ARC,
            $room,
            $event->getVisibility(),
            'event_log',
            null,
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
