<?php

namespace Mush\Room\Event;

use Mush\Game\Event\DayEvent;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
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
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewDay(DayEvent $event): void
    {
        if (!($room = $event->getRoom())) {
            return;
        }

        foreach ($room->getEquipments() as $equipment) {
            $equipmentNewDay = new DayEvent($room->getDaedalus(), $event->getTime());
            $equipmentNewDay->setGameEquipment($equipment);
            $this->eventDispatcher->dispatch($equipmentNewDay, DayEvent::NEW_DAY);
        }

        foreach ($room->getStatuses() as $status) {
            $statusNewDay = new DayEvent($room->getDaedalus(), $event->getTime());
            $statusNewDay->setStatus($status);
            $this->eventDispatcher->dispatch($statusNewDay, DayEvent::NEW_DAY);
        }
    }
}
