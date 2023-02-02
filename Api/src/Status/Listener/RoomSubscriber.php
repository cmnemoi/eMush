<?php

namespace Mush\Status\Listener;

use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Event\RoomEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
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
            RoomEvent::DELETE_PLACE => 'onDeletePlace',
        ];
    }

    public function onDeletePlace(RoomEvent $event): void
    {
        $place = $event->getPlace();

        /** @var Status $status */
        foreach ($place->getStatuses() as $status) {
            $statusEvent = new StatusEvent(
                $status->getName(),
                $place,
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);
        }
    }
}
