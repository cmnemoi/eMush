<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Event\Service\EventService;
use Mush\Place\Event\PlaceCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlaceCycleSubscriber implements EventSubscriberInterface
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
          $this->eventService = $eventService;
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

        foreach ($place->getEquipments() as $equipment) {
            $itemNewCycle = new EquipmentCycleEvent(
                $equipment,
                $place->getDaedalus(),
                $event->getReason(),
                $event->getTime()
            );
            $this->eventService->callEvent($itemNewCycle, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
        }
    }

    public function onNewDay(PlaceCycleEvent $event): void
    {
        $room = $event->getPlace();

        foreach ($room->getEquipments() as $equipment) {
            $equipmentNewDay = new EquipmentCycleEvent(
                $equipment,
                $room->getDaedalus(),
                $event->getReason(),
                $event->getTime()
            );

            $this->eventService->callEvent($equipmentNewDay, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);
        }
    }
}
