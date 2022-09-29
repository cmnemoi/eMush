<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\DoorEnum;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
                $statusEvent = new StatusEvent(
                    StatusEnum::FIRE,
                    $room,
                    EventEnum::NEW_CYCLE,
                    $date
                );
                $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
            }
        }

        return $numberOfNewFire;
    }

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewTremor = $this->getNumberOfIncident($daedalus);

        $isARoom = fn (Place $place) => $place->getType() === PlaceTypeEnum::ROOM;
        $hasPlayersInside = fn (Place $place) => $place->getPlayers()->getPlayerAlive()->count() > 0;

        $rooms = $daedalus->getRooms()->filter($isARoom)->filter($hasPlayersInside);

        $newTremorRooms = $this->randomService->getRandomElements($rooms->toArray(), $numberOfNewTremor);

        /** @var Place $room */
        foreach ($newTremorRooms as $room) {
            $roomEvent = new RoomEvent(
                $room,
                EventEnum::NEW_CYCLE,
                $date
            );
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
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
            $roomEvent = new RoomEvent(
                $room,
                EventEnum::NEW_CYCLE,
                $date
            );
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::ELECTRIC_ARC);
        }

        return $numberOfNewElectricArcs;
    }

    public function handleEquipmentBreak(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfEquipmentBroken = $this->getNumberOfIncident($daedalus);

        if ($numberOfEquipmentBroken > 0) {
            $criteria = new GameEquipmentCriteria($daedalus);
            $criteria->setNotInstanceOf([Door::class, Item::class]);
            $criteria->setBreakable(true);

            $daedalusEquipments = $this->gameEquipmentRepository->findByCriteria($criteria);

            $brokenEquipments = $this->randomService->getRandomElements($daedalusEquipments, $numberOfEquipmentBroken);

            foreach ($brokenEquipments as $gameEquipment) {
                if (!$gameEquipment->isBroken()) {
                    $statusEvent = new StatusEvent(
                        EquipmentStatusEnum::BROKEN,
                        $gameEquipment,
                        EventEnum::NEW_CYCLE,
                        new \DateTime()
                    );
                    $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
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

            $daedalusDoors = $this->gameEquipmentRepository->findByCriteria($criteria);

            $daedalusDoorsNames = array_map(fn (Door $door) => $door->getName(), $daedalusDoors);

            $breakableDoorsNames = array_filter($daedalusDoorsNames, fn (string $doorName) => DoorEnum::isBreakable($doorName));

            $breakableDoors = array_filter($daedalusDoors, fn (Door $door) => in_array($door->getName(), $breakableDoorsNames));

            $brokenDoors = $this->randomService->getRandomElements($breakableDoors, $numberOfDoorBroken);

            foreach ($brokenDoors as $door) {
                if (!$door->isBroken()) {
                    $statusEvent = new StatusEvent(
                        EquipmentStatusEnum::BROKEN,
                        $door,
                        EventEnum::NEW_CYCLE,
                        new \DateTime()
                    );
                    $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
                }
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
                    $playerEvent = new PlayerEvent(
                        $player,
                        EventEnum::NEW_CYCLE,
                        $date
                    );
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
                    $playerEvent = new PlayerEvent(
                        $player,
                        EventEnum::NEW_CYCLE,
                        $date
                    );
                    $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::METAL_PLATE);
                }
            }

            return $numberOfMetalPlates;
        } else {
            return 0;
        }
    }

    public function handleCrewDisease(Daedalus $daedalus, \DateTime $date): int
    {
        if (($playerCount = $daedalus->getPlayers()->getPlayerAlive()->count()) > 0) {
            $crewDiseaseRate = intval($this->getNumberOfIncident($daedalus) / $playerCount);
            $numberOfDiseasedPlayers = min($crewDiseaseRate, $playerCount);

            if ($crewDiseaseRate > 0) {
                $players = $daedalus->getPlayers()->getPlayerAlive();
                $diseasedPlayer = $this->randomService->getRandomElements($players->toArray(), $numberOfDiseasedPlayers);

                foreach ($diseasedPlayer as $player) {
                    $playerEvent = new PlayerEvent(
                        $player,
                        EventEnum::NEW_CYCLE,
                        $date
                    );
                    $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CYCLE_DISEASE);
                }
            }

            return $numberOfDiseasedPlayers;
        } else {
            return 0;
        }
    }

    // Incident number follows approximatively a Poisson distribution P(lambda)
    // where lambda = 3.3*10^(-3) * day^1.7 is the average number of incidents per cycle
    // @TODO : handle accumulated incidents
    private function getNumberOfIncident(Daedalus $daedalus): int
    {
        $averageIncidentsPerCycle = 3.3 * pow(10, -3) * $daedalus->getDay() ** 1.7;

        return $this->randomService->poissonRandom($averageIncidentsPerCycle);
    }
}
