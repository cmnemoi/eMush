<?php

namespace Mush\Room\Event;

use Mush\Game\Event\CycleEvent;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
{
    private RoomServiceInterface $roomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(RoomServiceInterface $roomService, EventDispatcherInterface $eventDispatcher)
    {
        $this->roomService = $roomService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event): void
    {
        if (!($room = $event->getRoom())) {
            return;
        }

        foreach ($room->getEquipments() as $equipment) {
            $itemNewCycle = new CycleEvent($room->getDaedalus(), $event->getTime());
            $itemNewCycle->setGameEquipment($equipment);
            $this->eventDispatcher->dispatch($itemNewCycle, CycleEvent::NEW_CYCLE);
        }
    }
}
