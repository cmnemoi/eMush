<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Service\EquipmentCycleHandlerServiceInterface;
use Mush\Game\Event\CycleEvent;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
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

    public static function getSubscribedEvents(): array
    {
        return [
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event): void
    {
        if (!($equipment = $event->getGameEquipment())) {
            return;
        }

        foreach ($equipment->getStatuses() as $status) {
            $statusNewCycle = new CycleEvent($event->getDaedalus(), $event->getTime());
            $statusNewCycle->setStatus($status);
            $this->eventDispatcher->dispatch($statusNewCycle, CycleEvent::NEW_CYCLE);
        }

        foreach ($equipment->getEquipment()->getMechanics() as $mechanic) {
            if ($cycleHandler = $this->equipmentCycleHandler->getEquipmentCycleHandler($mechanic)) {
                $cycleHandler->handleNewCycle($equipment, $event->getDaedalus(), $event->getTime());
            }
        }
    }
}
