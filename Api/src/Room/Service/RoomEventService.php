<?php

namespace Mush\Room\Service;

use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Service\GameConfigServiceInterface;
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
    private DifficultyConfig $difficultyConfig;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * RoomService constructor.
     */
    public function __construct(
        RandomServiceInterface $randomService,
        GameConfigServiceInterface $gameConfigService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->randomService = $randomService;
        $this->difficultyConfig = $gameConfigService->getDifficultyConfig();
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleIncident(Room $room, \DateTime $date): Room
    {
        //Tremors
        if ($this->randomService->isSuccessfull($this->difficultyConfig->getTremorRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }

        //Electric Arcs
        if ($this->randomService->isSuccessfull($this->difficultyConfig->getElectricArcRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }

        //Fire
        $this->handleFire($room, $date);

        return $room;
    }

    public function handleFire(Room $room, \DateTime $date): Room
    {
        $fireStatus = $room->getStatusByName(StatusEnum::FIRE);
        if ($fireStatus && !$fireStatus instanceof ChargeStatus) {
            throw new \LogicException('Fire is not a ChargedStatus');
        }

        if ($fireStatus && $fireStatus->getCharge() === 0) {
            //there is already a fire in the room
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::FIRE);

        //a secondary fire already started in this room this cycle OR no fire
        } elseif ($this->randomService->isSuccessfull($this->difficultyConfig->getStartingFireRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::CYCLE_FIRE);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);

            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::FIRE);
        }

        return $room;
    }

    public function propagateFire(Room $room, \DateTime $date): Room
    {
        foreach ($room->getDoors() as $door) {
            $adjacentRoom = $door->getOtherRoom($room);

            if ($this->randomService->isSuccessfull($this->difficultyConfig->getPropagatingFireRate())) {
                $roomEvent = new RoomEvent($adjacentRoom, $date);
                $roomEvent->setReason(RoomEventEnum::PROPAGATING_FIRE);
                $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
            }
        }

        return $room;
    }
}
