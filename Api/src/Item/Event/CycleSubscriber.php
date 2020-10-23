<?php

namespace Mush\Item\Event;

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

    public static function getSubscribedEvents()
    {
        return [
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event)
    {
        if (!($item = $event->getItem())) {
            return;
        }
    }
}
