<?php

namespace Mush\Player\Event;

use Mush\Game\Event\DayEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewDay(DayEvent $event)
    {
        if (!($player = $event->getPlayer())) {
            return;
        }

        foreach ($player->getItems() as $item) {
            $itemNewDay = new DayEvent($event->getTime());
            $itemNewDay->setItem($item);
            $this->eventDispatcher->dispatch($itemNewDay, DayEvent::NEW_DAY);
        }
    }
}
