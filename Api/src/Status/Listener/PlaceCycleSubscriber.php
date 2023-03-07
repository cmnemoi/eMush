<?php

namespace Mush\Status\Listener;

use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlaceCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlaceCycleEvent::PLACE_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(PlaceCycleEvent $event): void
    {
        $place = $event->getPlace();

        foreach ($place->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent(
                $status,
                $place,
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }
    }
}
