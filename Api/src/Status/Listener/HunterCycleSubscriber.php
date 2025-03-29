<?php

namespace Mush\Status\Listener;

use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HunterCycleEvent::HUNTER_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(HunterCycleEvent $event): void
    {
        $attackingHunters = $event->getDaedalus()->getHuntersAroundDaedalus();

        foreach ($attackingHunters as $hunter) {
            foreach ($hunter->getStatuses() as $status) {
                $statusNewCycle = new StatusCycleEvent(
                    $status,
                    $status->getOwner(),
                    $event->getTags(),
                    $event->getTime()
                );
                $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
            }
        }
    }
}
