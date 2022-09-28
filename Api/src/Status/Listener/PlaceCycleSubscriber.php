<?php

namespace Mush\Status\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlaceCycleSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
          $this->eventService = $eventDispatcher;
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
            $this->eventService->dispatch($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
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
            $this->eventService->dispatch($statusNewDay, StatusCycleEvent::STATUS_NEW_DAY);
        }
    }
}
