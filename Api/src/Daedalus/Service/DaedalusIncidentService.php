<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Event\EquipmentEventInterface;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEventInterface;
use Mush\Player\Event\PlayerEventInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\StatusEnum;
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

        $rooms = $daedalus->getRooms()->filter(fn (Place $place) => ($place->getType() === PlaceTypeEnum::ROOM));

        $newFireRooms = $this->randomService->getRandomElements($rooms->toArray(), $numberOfNewFire);

        /** @var Place $room */
        foreach ($newFireRooms as $room) {
            if (!$room->hasStatus(StatusEnum::FIRE)) {
                $roomEvent = new RoomEventInterface(
                    $room,
                    EventEnum::NEW_CYCLE,
                    $date
                );
                $this->eventDispatcher->dispatch($roomEvent, RoomEventInterface::STARTING_FIRE);
            }
        }

        return $numberOfNewFire;
    }

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewTremor = $this->getNumberOfIncident($daedalus);

        $rooms = $daedalus->getRooms()->filter(fn (Place $place) => ($place->getType() === PlaceTypeEnum::ROOM));

        $newTremorRooms = $this->randomService->getRandomElements($rooms->toArray(), $numberOfNewTremor);

        /** @var Place $room */
        foreach ($newTremorRooms as $room) {
            $roomEvent = new RoomEventInterface(
                $room,
                EventEnum::NEW_CYCLE,
                $date
            );
            $this->eventDispatcher->dispatch($roomEvent, RoomEventInterface::TREMOR);
        }

        return $numberOfNewTremor;
    }

    public function handleElectricArcEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewElectricArcs = $this->getNumberOfIncident($daedalus);

        $rooms = $daedalus->getRooms()->filter(fn (Place $place) => ($place->getType() === PlaceTypeEnum::ROOM));

        $newElectricArcs = $this->randomService->getRandomElements($rooms->toArray(), $numberOfNewElectricArcs);

        /** @var Place $room */
        foreach ($newElectricArcs as $room) {
            $roomEvent = new RoomEventInterface(
                $room,
                EventEnum::NEW_CYCLE,
                $date
            );
            $this->eventDispatcher->dispatch($roomEvent, RoomEventInterface::ELECTRIC_ARC);
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
                if (!$gameEquipment->isBroken()) {
                    $equipmentEvent = new EquipmentEventInterface(
                        $gameEquipment,
                        $gameEquipment->getCurrentPlace(),
                        VisibilityEnum::HIDDEN,
                        EventEnum::NEW_CYCLE,
                        $date
                    );
                    $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEventInterface::EQUIPMENT_BROKEN);
                }
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
                $equipmentEvent = new EquipmentEventInterface(
                    $door,
                    $door->getRooms()->first(),
                    VisibilityEnum::HIDDEN,
                    EventEnum::NEW_CYCLE,
                    $date
                );
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEventInterface::EQUIPMENT_BROKEN);
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
                    $playerEvent = new PlayerEventInterface(
                        $player,
                        EventEnum::NEW_CYCLE,
                        $date
                    );
                    $this->eventDispatcher->dispatch($playerEvent, PlayerEventInterface::PANIC_CRISIS);
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
                    $playerEvent = new PlayerEventInterface(
                        $player,
                        EventEnum::NEW_CYCLE,
                        $date
                    );
                    $this->eventDispatcher->dispatch($playerEvent, PlayerEventInterface::METAL_PLATE);
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
        $rate = intval($daedalus->getDay() / 4);

        return $this->randomService->random(0, $rate);
    }
}
