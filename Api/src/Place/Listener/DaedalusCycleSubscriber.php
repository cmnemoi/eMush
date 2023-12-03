<?php

namespace Mush\Place\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Event\PlaceCycleEvent;
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
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::ROOMS],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        foreach ($event->getDaedalus()->getRooms() as $place) {
            $newRoomCycle = new PlaceCycleEvent(
                $place,
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($newRoomCycle, PlaceCycleEvent::PLACE_NEW_CYCLE);
        }
    }
}
