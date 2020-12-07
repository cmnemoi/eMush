<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Service\EquipmentCycleHandlerServiceInterface;
use Mush\Game\Event\DayEvent;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
{
    private RoomServiceInterface $roomService;
    private EventDispatcherInterface $eventDispatcher;
    private EquipmentCycleHandlerServiceInterface $equipmentCycleHandler;

    public function __construct(
        RoomServiceInterface $roomService,
        EquipmentCycleHandlerServiceInterface $equipmentCycleHandler,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->roomService = $roomService;
        $this->eventDispatcher = $eventDispatcher;
        $this->equipmentCycleHandler = $equipmentCycleHandler;
    }

    public static function getSubscribedEvents()
    {
        return [
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewDay(DayEvent $event)
    {
        if (!($equipment = $event->getGameEquipment())) {
            return;
        }

        foreach ($equipment->getStatuses() as $status) {
            $statusNewDay = new DayEvent($event->getDaedalus(), $event->getTime());
            $statusNewDay->setStatus($status);
            $this->eventDispatcher->dispatch($statusNewDay, DayEvent::NEW_DAY);
        }

        foreach ($equipment->getEquipment()->getMechanics() as $mechanics) {
            if ($cycleHandler = $this->equipmentCycleHandler->getEquipmentCycleHandler($mechanics)) {
                $cycleHandler->handleNewCycle($equipment, $event->getDaedalus(), $event->getTime());
            }
        }
    }
}
