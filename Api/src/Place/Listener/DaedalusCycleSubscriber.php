<?php

namespace Mush\Place\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Lock\LockFactory;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;

    private StatusServiceInterface $statusService;
    private LockFactory $lockFactory;

    public function __construct(
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
        LockFactory $lockFactory
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
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
            $this->handleFirePropagation($event);
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

    private function handleFirePropagation(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $difficultyConfig = $daedalus->getGameConfig()->getDifficultyConfig();

        $roomsOnFire = $daedalus->getRooms()->getAllWithStatus(StatusEnum::FIRE);
        $maxFireSpread = min($difficultyConfig->getMaximumAllowedSpreadingFires(), $roomsOnFire->count());

        /** @var Place $room */
        foreach ($this->randomService->getRandomElements($roomsOnFire->toArray(), $roomsOnFire->count()) as $room) {
            if ($maxFireSpread >= 1) {
                if ($this->randomService->isSuccessful($difficultyConfig->getPropagatingFireRate())) {
                    $adjacentRoomsNotOnFire = $room->getAdjacentRoomsAsPlaceCollection()->getAllWithoutStatus(StatusEnum::FIRE);
                    if (!$adjacentRoomsNotOnFire->isEmpty()) {
                        /** @var Place $spreadingRoom */
                        $spreadingRoom = $this->randomService->getRandomElement($adjacentRoomsNotOnFire->toArray());

                        $this->statusService->createStatusFromName(
                            StatusEnum::FIRE,
                            $spreadingRoom,
                            [RoomEventEnum::PROPAGATING_FIRE],
                            $event->getTime()
                        );

                        --$maxFireSpread;
                    }
                }
            }
        }
    }
}
