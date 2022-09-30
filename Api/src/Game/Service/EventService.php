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

    public function callEvent(AbstractGameEvent $event, string $name): void
    {
        $event->setEvent($name);
        $this->eventDispatcher->dispatch($event, $name);
    }

}