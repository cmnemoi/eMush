<?php

namespace Mush\Place\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Event\RoomEvent;
use Mush\Place\Service\PlaceServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private PlaceServiceInterface $placeService;
    private EventServiceInterface $eventService;

    public function __construct(
        PlaceServiceInterface $placeService,
        EventServiceInterface $eventService
    ) {
        $this->placeService = $placeService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::DELETE_DAEDALUS => 'onDeleteDaedalus',
        ];
    }

    public function onDeleteDaedalus(DaedalusEvent $event): void
    {
        foreach ($event->getDaedalus()->getRooms() as $place) {
            $deleteEvent = new RoomEvent(
                $place,
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($deleteEvent, RoomEvent::DELETE_PLACE);

            $this->placeService->delete($place);
        }
    }
}
