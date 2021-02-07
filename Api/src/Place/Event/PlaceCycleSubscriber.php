<?php

namespace Mush\Place\Event;

use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Place\Service\RoomEventServiceInterface;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlaceCycleSubscriber implements EventSubscriberInterface
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
            PlaceCycleEvent::PLACE_NEW_CYCLE => 'onNewCycle',
            PlaceCycleEvent::PLACE_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(PlaceCycleEvent $event): void
    {
        $place = $event->getPlace();

        //handle events
        $this->roomEventService->handleIncident($place, $event->getTime());

        foreach ($place->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent($status, $place, $place->getDaedalus(), $event->getTime());
            $this->eventDispatcher->dispatch($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }

        foreach ($place->getEquipments() as $equipment) {
            $itemNewCycle = new EquipmentCycleEvent($equipment, $place->getDaedalus(), $event->getTime());
            $this->eventDispatcher->dispatch($itemNewCycle, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
        }
    }

    public function onNewDay(PlaceCycleEvent $event): void
    {
        $room = $event->getPlace();

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
