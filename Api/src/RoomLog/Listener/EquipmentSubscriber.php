<?php

namespace Mush\RoomLog\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_BROKEN => ['onEquipmentBroken', 10],
            EquipmentEvent::EQUIPMENT_DESTROYED => ['onEquipmentDestroyed', 10],
        ];
    }

    public function onEquipmentBroken(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if ($equipment instanceof Door) {
            $rooms = $equipment->getRooms()->toArray();
        } else {
            $rooms = [$equipment->getCurrentPlace()];
        }

        foreach ($rooms as $room) {
            $this->roomLogService->createLog(
                LogEnum::EQUIPMENT_BROKEN,
                $room,
                $event->getVisibility(),
                'event_log',
                null,
                $equipment,
                null,
                $event->getTime()
            );
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $place = $equipment->getCurrentPlace();

        $this->roomLogService->createLog(
            LogEnum::EQUIPMENT_DESTROYED,
            $place,
            $event->getVisibility(),
            'event_log',
            null,
            $equipment,
            null,
            $event->getTime()
        );
    }
}
