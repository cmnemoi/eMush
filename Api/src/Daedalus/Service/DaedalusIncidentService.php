<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DaedalusIncidentService implements DaedalusIncidentServiceInterface
{
    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;
    private GameEquipmentRepository $gameEquipmentRepository;

    public function __construct(
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentRepository $gameEquipmentRepository
    ) {
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
        $this->gameEquipmentRepository = $gameEquipmentRepository;
    }

    public function handleFireEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewFire = $this->getNumberOfIncident($daedalus);

        $newFireRooms = $this->randomService->getRandomElements($daedalus->getRooms()->toArray(), $numberOfNewFire);

        /** @var Place $room */
        foreach ($newFireRooms as $room) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::CYCLE_FIRE);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
        }

        return $numberOfNewFire;
    }

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewTremor = $this->getNumberOfIncident($daedalus);

        $newTremorRooms = $this->randomService->getRandomElements($daedalus->getRooms()->toArray(), $numberOfNewTremor);

        /** @var Place $room */
        foreach ($newTremorRooms as $room) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::TREMOR);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }

        return $numberOfNewTremor;
    }

    public function handleElectricArcEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewElectricArcs = $this->getNumberOfIncident($daedalus);

        $newElectricArcs = $this->randomService->getRandomElements($daedalus->getRooms()->toArray(), $numberOfNewElectricArcs);

        /** @var Place $room */
        foreach ($newElectricArcs as $room) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::ELECTRIC_ARC);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::ELECTRIC_ARC);
        }

        return $numberOfNewElectricArcs;
    }

    public function handleEquipmentBreak(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfEquipmentBroken = $this->getNumberOfIncident($daedalus);

        if ($numberOfEquipmentBroken > 0) {
            $criteria = new GameEquipmentCriteria($daedalus);
            $criteria->setNotInstanceOf([Door::class]);
            $criteria->setBreakable(true);

            $daedalusEquipments = $this->gameEquipmentRepository->findByCriteria($criteria);

            $brokenEquipments = $this->randomService->getRandomElements($daedalusEquipments, $numberOfEquipmentBroken);

            foreach ($brokenEquipments as $gameEquipment) {
                $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::HIDDEN, $date);
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            }
        }

        return $numberOfEquipmentBroken;
    }

    public function handleDoorBreak(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfDoorBroken = $this->getNumberOfIncident($daedalus);

        if ($numberOfDoorBroken > 0) {
            $criteria = new GameEquipmentCriteria($daedalus);
            $criteria->setInstanceOf([Door::class]);
            $criteria->setBreakable(true);

            $daedalusDoor = $this->gameEquipmentRepository->findByCriteria($criteria);

            $brokenDoors = $this->randomService->getRandomElements($daedalusDoor, $numberOfDoorBroken);

            foreach ($brokenDoors as $door) {
                $equipmentEvent = new EquipmentEvent($door, VisibilityEnum::HIDDEN, $date);
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            }
        }

        return $numberOfDoorBroken;
    }

    public function handlePanicCrisis(Daedalus $daedalus, \DateTime $date): int
    {
        if (($playerCount = $daedalus->getPlayers()->getPlayerAlive()->count()) > 0) {
            $panicCrisisRate = intval($this->getNumberOfIncident($daedalus) / $playerCount);
            $numberOfPanicCrisis = min($panicCrisisRate, $playerCount);

            if ($numberOfPanicCrisis > 0) {
                $humans = $daedalus->getPlayers()->getPlayerAlive()->getHumanPlayer();
                $humansCrisis = $this->randomService->getRandomElements($humans->toArray(), $numberOfPanicCrisis);

                foreach ($humansCrisis as $player) {
                    $playerEvent = new PlayerEvent($player, $date);
                    $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::PANIC_CRISIS);
                }
            }

            return $numberOfPanicCrisis;
        } else {
            return 0;
        }
    }

    public function handleMetalPlates(Daedalus $daedalus, \DateTime $date): int
    {
        if (($playerCount = $daedalus->getPlayers()->getPlayerAlive()->count()) > 0) {
            $metalPlateRate = intval($this->getNumberOfIncident($daedalus) / $playerCount);
            $numberOfMetalPlates = min($metalPlateRate, $playerCount);

            if ($numberOfMetalPlates > 0) {
                $players = $daedalus->getPlayers()->getPlayerAlive();
                $metalPlatesPlayer = $this->randomService->getRandomElements($players->toArray(), $numberOfMetalPlates);

                foreach ($metalPlatesPlayer as $player) {
                    $playerEvent = new PlayerEvent($player, $date);
                    $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::METAL_PLATE);
                }
            }

            return $numberOfMetalPlates;
        } else {
            return 0;
        }
    }

    //Each cycle get 0 to day event
    //@TODO: to be improved
    private function getNumberOfIncident(Daedalus $daedalus): int
    {
        $rate = intval($daedalus->getDay() / 2);

        return $this->randomService->random(0, $rate);
    }
}
