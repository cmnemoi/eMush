<?php

namespace Mush\Place\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Place\Entity\Place;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\Game\Service\EventServiceInterface;
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
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => 'onNewCycle',
            DaedalusCycleEvent::DAEDALUS_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        foreach ($event->getDaedalus()->getRooms() as $place) {
            $newRoomCycle = new PlaceCycleEvent(
                $place,
                $event->getReasons()[0],
                $event->getTime()
            );
            $this->eventService->callEvent($newRoomCycle, PlaceCycleEvent::PLACE_NEW_CYCLE);
        }
    }

    public function onNewDay(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        /** @var Place $place */
        foreach ($daedalus->getRooms() as $place) {
            $newRoomDay = new PlaceCycleEvent(
                $place,
                $event->getReasons()[0],
                $event->getTime()
            );
            $this->eventService->callEvent($newRoomDay, PlaceCycleEvent::PLACE_NEW_DAY);
        }
    }
}
