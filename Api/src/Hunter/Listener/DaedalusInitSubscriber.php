<?php

namespace Mush\Hunter\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusInitSubscriber implements EventSubscriberInterface
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
            DaedalusInitEvent::NEW_DAEDALUS => ['onNewDaedalus', EventPriorityEnum::LOW], // do this after space creation
        ];
    }

    public function onNewDaedalus(DaedalusInitEvent $event): void
    {
        $poolHunterEvent = new HunterPoolEvent(
            $event->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime()
        );

        $this->eventService->callEvent($poolHunterEvent, HunterPoolEvent::UNPOOL_HUNTERS);
    }
}
