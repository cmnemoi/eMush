<?php

namespace Mush\Game\Service;

use Mush\Game\Event\AbstractGameEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventService implements EventServiceInterface
{

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function callEvent(AbstractGameEvent $event, string $name, AbstractGameEvent $caller = null): void
    {
        if ($caller !== null) $event->setReason(array_merge($event->getReasons()[0], $caller->getReasons()));
        $event->setEventName($name);
        $this->eventDispatcher->dispatch($event, $name);
    }

}