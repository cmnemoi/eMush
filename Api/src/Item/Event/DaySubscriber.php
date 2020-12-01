<?php

namespace Mush\Item\Event;

use Mush\Game\Event\DayEvent;
use Mush\Item\Service\ItemCycleHandlerServiceInterface;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
{
    private RoomServiceInterface $roomService;
    private EventDispatcherInterface $eventDispatcher;
    private ItemCycleHandlerServiceInterface $itemCycleHandler;

    public function __construct(
        RoomServiceInterface $roomService,
        ItemCycleHandlerServiceInterface $itemCycleHandler,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->roomService = $roomService;
        $this->eventDispatcher = $eventDispatcher;
        $this->itemCycleHandler = $itemCycleHandler;
    }

    public static function getSubscribedEvents()
    {
        return [
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewDay(DayEvent $event)
    {
        if (!($item = $event->getGameItem())) {
            return;
        }

        foreach ($item->getStatuses() as $status) {
            $statusNewDay = new DayEvent($event->getDaedalus(), $event->getTime());
            $statusNewDay->setStatus($status);
            $this->eventDispatcher->dispatch($statusNewDay, DayEvent::NEW_DAY);
        }

        foreach ($item->getItem()->getTypes() as $itemType) {
            if ($cycleHandler = $this->itemCycleHandler->getItemCycleHandler($itemType)) {
                $cycleHandler->handleNewCycle($item, $event->getDaedalus(), $event->getTime());
            }
        }
    }
}
