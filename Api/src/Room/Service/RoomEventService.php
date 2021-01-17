<?php

namespace Mush\Room\Service;

use Mush\Game\Service\RandomServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEventEnum;
use Mush\Room\Event\RoomEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RoomEventService implements RoomEventServiceInterface
{
    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleIncident(Room $room, \DateTime $date): Room
    {
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();

        //Tremors
        if ($this->randomService->isSuccessfull($difficultyConfig->getTremorRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }

        //Electric Arcs
        if ($this->randomService->isSuccessfull($difficultyConfig->getElectricArcRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::ELECTRIC_ARC);
        }

        //Fire
        $this->handleNewFire($room, $date);

        return $room;
    }

    public function handleNewFire(Room $room, \DateTime $date): Room
    {
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $fireStatus = $room->getStatusByName(StatusEnum::FIRE);

        if ($fireStatus && !$fireStatus instanceof ChargeStatus) {
            throw new \LogicException('Fire is not a ChargedStatus');
        }

        if ($fireStatus === null && $this->randomService->isSuccessfull($difficultyConfig->getStartingFireRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::CYCLE_FIRE);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
        }

        return $room;
    }
}
