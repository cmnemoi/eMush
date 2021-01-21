<?php

namespace Mush\Room\Event;

use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Room\Service\RoomEventServiceInterface;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomCycleSubscriber implements EventSubscriberInterface
{
    private RoomEventServiceInterface $roomEventService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(RoomEventServiceInterface $roomEventService, EventDispatcherInterface $eventDispatcher)
    {
        $this->roomEventService = $roomEventService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomCycleEvent::ROOM_NEW_CYCLE => 'onNewCycle',
            RoomCycleEvent::ROOM_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(RoomCycleEvent $event): void
    {
        $room = $event->getRoom();

        //handle events
        $this->roomEventService->handleIncident($room, $event->getTime());

        foreach ($room->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent($status, $room, $room->getDaedalus(), $event->getTime());
            $this->eventDispatcher->dispatch($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }

        foreach ($room->getEquipments() as $equipment) {
            $itemNewCycle = new EquipmentCycleEvent($equipment, $room->getDaedalus(), $event->getTime());
            $this->eventDispatcher->dispatch($itemNewCycle, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
        }
    }

    public function onNewDay(RoomCycleEvent $event): void
    {
        $room = $event->getRoom();

        foreach ($room->getEquipments() as $equipment) {
            $equipmentNewDay = new EquipmentCycleEvent($equipment, $room->getDaedalus(), $event->getTime());

            $this->eventDispatcher->dispatch($equipmentNewDay, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);
        }

        foreach ($room->getStatuses() as $status) {
            $statusNewDay = new StatusCycleEvent($status, $room, $room->getDaedalus(), $event->getTime());
            $this->eventDispatcher->dispatch($statusNewDay, StatusCycleEvent::STATUS_NEW_DAY);
        }
    }
}
