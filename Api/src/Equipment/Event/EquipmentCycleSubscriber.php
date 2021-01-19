<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\EquipmentCycleHandlerServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Room\Service\RoomServiceInterface;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentCycleSubscriber implements EventSubscriberInterface
{
    private RoomServiceInterface $roomService;
    private EventDispatcherInterface $eventDispatcher;
    private EquipmentCycleHandlerServiceInterface $equipmentCycleHandler;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        RoomServiceInterface $roomService,
        GameEquipmentServiceInterface $gameEquipmentService,
        EquipmentCycleHandlerServiceInterface $equipmentCycleHandler,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->roomService = $roomService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventDispatcher = $eventDispatcher;
        $this->equipmentCycleHandler = $equipmentCycleHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE => 'onNewCycle',
            EquipmentCycleEvent::EQUIPMENT_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        //each equipment as a chance to break
        if (!$equipment instanceof GameItem) {
            $this->gameEquipmentService->handleBreakCycle($equipment, $event->getTime());
        }

        foreach ($equipment->getStatuses() as $status) {
            if ($status->getPlayer() === null) {
                $statusNewCycle = new StatusCycleEvent($status, $equipment, $event->getDaedalus(), $event->getTime());
                $this->eventDispatcher->dispatch($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
            }
        }

        foreach ($equipment->getEquipment()->getMechanics() as $mechanic) {
            if ($cycleHandler = $this->equipmentCycleHandler->getEquipmentCycleHandler($mechanic)) {
                $cycleHandler->handleNewCycle($equipment, $event->getDaedalus(), $event->getTime());
            }
        }
    }

    public function onNewDay(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        foreach ($equipment->getStatuses() as $status) {
            if ($status->getPlayer() === null) {
                $statusNewDay = new StatusCycleEvent($status, $equipment, $event->getDaedalus(), $event->getTime());
                $this->eventDispatcher->dispatch($statusNewDay, StatusCycleEvent::STATUS_NEW_DAY);
            }
        }

        foreach ($equipment->getEquipment()->getMechanics() as $mechanics) {
            if ($cycleHandler = $this->equipmentCycleHandler->getEquipmentCycleHandler($mechanics)) {
                $cycleHandler->handleNewDay($equipment, $event->getDaedalus(), $event->getTime());
            }
        }
    }
}
