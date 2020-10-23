<?php

namespace Mush\Player\Event;

use Mush\Game\Event\CycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
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
        if (!($player = $event->getPlayer())) {
            return;
        }

        foreach ($player->getItems() as $item) {
            $itemNewCycle = new CycleEvent($event->getTime());
            $itemNewCycle->setItem($item);
            $this->eventDispatcher->dispatch($itemNewCycle, CycleEvent::NEW_CYCLE);
        }
    }
}