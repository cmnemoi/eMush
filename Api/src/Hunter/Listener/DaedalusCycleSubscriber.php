<?php

namespace Mush\Hunter\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService
    ) {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::HUNTERS],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $event = new HunterCycleEvent($event->getDaedalus(), $event->getTags(), $event->getTime());
        $this->eventService->callEvent($event, HunterCycleEvent::HUNTER_NEW_CYCLE);
    }
}
