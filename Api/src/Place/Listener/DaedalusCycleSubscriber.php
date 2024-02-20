<?php

namespace Mush\Place\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Event\PlaceCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Lock\LockFactory;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private LockFactory $lockFactory;

    public function __construct(
        EventServiceInterface $eventService,
        LockFactory $lockFactory
    ) {
        $this->eventService = $eventService;
        $this->lockFactory = $lockFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::ROOMS],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_cycle');
        $lock->acquire(true);

        try {
            $this->handlePlacesNewCycle($event);
        } finally {
            $lock->release();
        }
    }

    private function handlePlacesNewCycle(DaedalusCycleEvent $event): void
    {
        /** @var Place $place */
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
