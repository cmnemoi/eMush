<?php

namespace Mush\Room\Event;

use Mush\Game\Event\CycleEvent;
use Mush\Room\Service\RoomEventServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
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
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event): void
    {
        if (!($room = $event->getRoom())) {
            return;
        }

        foreach ($room->getStatuses() as $status) {
            $statusNewCycle = new CycleEvent($event->getDaedalus(), $event->getTime());
            $statusNewCycle->setStatus($status);
            $this->eventDispatcher->dispatch($statusNewCycle, CycleEvent::NEW_CYCLE);
        }

        //handle events
        $this->roomEventService->handleIncident($room, $event->getTime());

        foreach ($room->getEquipments() as $equipment) {
            $itemNewCycle = new CycleEvent($room->getDaedalus(), $event->getTime());
            $itemNewCycle->setGameEquipment($equipment);
            $this->eventDispatcher->dispatch($itemNewCycle, CycleEvent::NEW_CYCLE);
        }
    }
}
