<?php

namespace Mush\Place\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Event\PlaceCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
          $this->eventService = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => 'onNewCycle',
            DaedalusCycleEvent::DAEDALUS_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        foreach ($event->getDaedalus()->getRooms() as $place) {
            $newRoomCycle = new PlaceCycleEvent(
                $place,
                $event->getReason(),
                $event->getTime()
            );
            $this->eventService->dispatch($newRoomCycle, PlaceCycleEvent::PLACE_NEW_CYCLE);
        }
    }

    public function onNewDay(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        /** @var Place $place */
        foreach ($daedalus->getRooms() as $place) {
            $newRoomDay = new PlaceCycleEvent(
                $place,
                $event->getReason(),
                $event->getTime()
            );
            $this->eventService->dispatch($newRoomDay, PlaceCycleEvent::PLACE_NEW_DAY);
        }
    }
}
