<?php

namespace Mush\Item\Event;

use Mush\Game\Event\CycleEvent;
use Mush\Item\Service\ItemCycleHandlerServiceInterface;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
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
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event)
    {
        if (!($item = $event->getGameItem())) {
            return;
        }

        foreach ($item->getStatuses() as $status) {
            $statusNewCycle = new CycleEvent($event->getDaedalus(), $event->getTime());
            $statusNewCycle->setStatus($status);
            $this->eventDispatcher->dispatch($statusNewCycle, CycleEvent::NEW_CYCLE);
        }

        foreach ($item->getItem()->getTypes() as $itemType) {
            if ($cycleHandler = $this->itemCycleHandler->getItemCycleHandler($itemType)) {
                $cycleHandler->handleNewCycle($item, $event->getDaedalus(), $event->getTime());
            }
        }
    }
}
