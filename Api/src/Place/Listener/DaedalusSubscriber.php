<?php

namespace Mush\Place\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Place\Event\RoomEvent;
use Mush\Place\Service\PlaceServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private PlaceServiceInterface $placeService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        PlaceServiceInterface $placeService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->placeService = $placeService;
        $this->eventDispatcher = $eventDispatcher;
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
                $event->getReason(),
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($deleteEvent, RoomEvent::DELETE_PLACE);

            $this->placeService->delete($place);
        }
    }
}
