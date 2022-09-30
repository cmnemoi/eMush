<?php

namespace Mush\Status\Listener;

use Mush\Place\Event\PlaceCycleEvent;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Game\Service\EventServiceInterface;
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
            PlaceCycleEvent::PLACE_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(PlaceCycleEvent $event): void
    {
        $place = $event->getPlace();

        foreach ($place->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent(
                $status,
                $place,
                $event->getReason(),
                $event->getTime()
            );
            $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }
    }

    public function onNewDay(PlaceCycleEvent $event): void
    {
        $room = $event->getPlace();

        foreach ($room->getStatuses() as $status) {
            $statusNewDay = new StatusCycleEvent(
                $status,
                $room,
                $event->getReason(),
                $event->getTime()
            );
            $this->eventService->callEvent($statusNewDay, StatusCycleEvent::STATUS_NEW_DAY);
        }
    }
}
