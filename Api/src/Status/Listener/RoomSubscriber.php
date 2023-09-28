<?php

namespace Mush\Status\Listener;

use Mush\Place\Event\RoomEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService
    ) {
        $this->statusService = $statusService;
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
            $this->statusService->removeStatus(
                $status->getName(),
                $place,
                $event->getTags(),
                $event->getTime()
            );
        }
    }
}
