<?php

namespace Mush\Status\Listener;

use Mush\Place\Event\RoomEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
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
                $event->getReason(),
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_REMOVED);
        }
    }
}
