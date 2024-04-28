<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\DoorEnum;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Psr\Log\LoggerInterface;

class DaedalusIncidentService implements DaedalusIncidentServiceInterface
{
    private const ALPHA_MULTIPLIER = 3;

    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;
    private GameEquipmentRepository $gameEquipmentRepository;
    private LoggerInterface $logger;

    private StatusServiceInterface $statusService;

    public function __construct(
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService,
        GameEquipmentRepository $gameEquipmentRepository,
        LoggerInterface $logger,
        StatusServiceInterface $statusService
    ) {
        $this->randomService = $randomService;
        $this->eventService = $eventService;
        $this->gameEquipmentRepository = $gameEquipmentRepository;
        $this->logger = $logger;
        $this->statusService = $statusService;
    }

    public function handleFireEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewFire = $this->getNumberOfIncident($daedalus);

        $rooms = $daedalus->getRoomsInFire();

        $newFireRooms = $this->randomService->getRandomElements($rooms->toArray(), $numberOfNewFire);

        /** @var Place $room */
        foreach ($newFireRooms as $room) {
            if (!$room->hasStatus(StatusEnum::FIRE)) {
                $this->statusService->createStatusFromName(
                    StatusEnum::FIRE,
                    $room,
                    [EventEnum::NEW_CYCLE, StatusEnum::FIRE],
                    $date
                );
            }
        }

        return $numberOfNewFire;
    }

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewTremor = $this->getNumberOfIncident($daedalus);

        $isARoom = static fn (Place $place): bool => $place->getType() === PlaceTypeEnum::ROOM;
        $hasPlayersInside = static fn (Place $place): bool => $place->getPlayers()->getPlayerAlive()->count() > 0;

        $rooms = $daedalus->getRooms()->filter($isARoom)->filter($hasPlayersInside);

        $newTremorRooms = $this->randomService->getRandomElements($rooms->toArray(), $numberOfNewTremor);

        /** @var Place $room */
        foreach ($newTremorRooms as $room) {
            $roomEvent = new RoomEvent(
                $room,
                [EventEnum::NEW_CYCLE, RoomEvent::TREMOR],
                $date
            );
            $this->eventService->callEvent($roomEvent, RoomEvent::TREMOR);
        }

        return $numberOfNewTremor;
    }

    public function handleElectricArcEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewElectricArcs = $this->getNumberOfIncident($daedalus);

        $rooms = $daedalus->getRooms()->filter(static fn (Place $place) => ($place->getType() === PlaceTypeEnum::ROOM));

        $newElectricArcs = $this->randomService->getRandomElements($rooms->toArray(), $numberOfNewElectricArcs);

        /** @var Place $room */
        foreach ($newElectricArcs as $room) {
            $roomEvent = new RoomEvent(
                $room,
                [EventEnum::NEW_CYCLE, RoomEvent::ELECTRIC_ARC],
                $date
            );
            $this->eventService->callEvent($roomEvent, RoomEvent::ELECTRIC_ARC);
        }

        return $numberOfNewElectricArcs;
    }

    public function handleEquipmentBreak(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfEquipmentBroken = $this->getNumberOfIncident($daedalus);

        if ($numberOfEquipmentBroken > 0) {
            $workingEquipmentBreakRateDistribution = $this->getWorkingEquipmentBreakRateDistribution($daedalus);

            // If there is no working equipment, we don't have to break anything, so we return 0
            if ($workingEquipmentBreakRateDistribution->isEmpty()) {
                return 0;
            }
            // If there is less working equipment than the number of equipment we want to break
            // we break the number of working equipment instead to avoid an error
            $numberOfEquipmentBroken = min($numberOfEquipmentBroken, $workingEquipmentBreakRateDistribution->count());

            $brokenEquipments = $this
                ->randomService
                ->getRandomDaedalusEquipmentFromProbaCollection(
                    $workingEquipmentBreakRateDistribution,
                    $numberOfEquipmentBroken,
                    $daedalus
                );

            foreach ($brokenEquipments as $gameEquipment) {
                if (!$gameEquipment->isBroken()) {
                    $this->statusService->createStatusFromName(
                        EquipmentStatusEnum::BROKEN,
                        $gameEquipment,
                        [EventEnum::NEW_CYCLE, EquipmentEvent::EQUIPMENT_BROKEN],
                        new \DateTime()
                    );
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

            $daedalusDoorsNames = array_map(static fn (Door $door) => $door->getName(), $daedalusDoors);

            $breakableDoorsNames = array_filter($daedalusDoorsNames, static fn (string $doorName) => DoorEnum::isBreakable($doorName));

            $breakableDoors = array_filter($daedalusDoors, static fn (Door $door) => \in_array($door->getName(), $breakableDoorsNames, true));

            $brokenDoors = $this->randomService->getRandomElements($breakableDoors, $numberOfDoorBroken);

            foreach ($brokenDoors as $door) {
                if (!$door->isBroken()) {
                    $this->statusService->createStatusFromName(
                        EquipmentStatusEnum::BROKEN,
                        $door,
                        [EventEnum::NEW_CYCLE, EquipmentEvent::DOOR_BROKEN],
                        new \DateTime()
                    );
                }
            }
        }

        return $numberOfDoorBroken;
    }

    public function handlePanicCrisis(Daedalus $daedalus, \DateTime $date): int
    {
        $humanPlayers = $daedalus->getPlayers()->getPlayerAlive()->getHumanPlayer();
        if ($humanPlayers->count() > 0) {
            $numberOfPanicCrisis = min($this->getNumberOfIncident($daedalus), $humanPlayers->count());

            if ($numberOfPanicCrisis > 0) {
                $humansCrisis = $this->randomService->getRandomElements($humanPlayers->toArray(), $numberOfPanicCrisis);

                foreach ($humansCrisis as $player) {
                    $playerEvent = new PlayerEvent(
                        $player,
                        [EventEnum::NEW_CYCLE, PlayerEvent::PANIC_CRISIS],
                        $date
                    );
                    $this->eventService->callEvent($playerEvent, PlayerEvent::PANIC_CRISIS);
                }
            }

            return $numberOfPanicCrisis;
        }

        return 0;
    }

    public function handleMetalPlates(Daedalus $daedalus, \DateTime $date): int
    {
        $alivePlayers = $daedalus->getPlayers()->getPlayerAlive();
        if ($alivePlayers->count() > 0) {
            $numberOfMetalPlates = min($this->getNumberOfIncident($daedalus), $alivePlayers->count());

            if ($numberOfMetalPlates > 0) {
                $metalPlatesPlayer = $this->randomService->getRandomElements($alivePlayers->toArray(), $numberOfMetalPlates);

                foreach ($metalPlatesPlayer as $player) {
                    $playerEvent = new PlayerEvent(
                        $player,
                        [EventEnum::NEW_CYCLE, PlayerEvent::METAL_PLATE],
                        $date
                    );
                    $this->eventService->callEvent($playerEvent, PlayerEvent::METAL_PLATE);
                }
            }

            return $numberOfMetalPlates;
        }

        return 0;
    }

    public function handleCrewDisease(Daedalus $daedalus, \DateTime $date): int
    {
        $humanAlivePlayers = $daedalus->getPlayers()->getPlayerAlive()->getHumanPlayer();
        if ($humanAlivePlayers->count() > 0) {
            $numberOfDiseasedPlayers = min($this->getNumberOfIncident($daedalus), $humanAlivePlayers->count());

            if ($numberOfDiseasedPlayers > 0) {
                $diseasedPlayer = $this->randomService->getRandomElements($humanAlivePlayers->toArray(), $numberOfDiseasedPlayers);

                foreach ($diseasedPlayer as $player) {
                    $playerEvent = new PlayerEvent(
                        $player,
                        [EventEnum::NEW_CYCLE, PlayerEvent::CYCLE_DISEASE],
                        $date
                    );
                    $this->eventService->callEvent($playerEvent, PlayerEvent::CYCLE_DISEASE);
                }
            }

            return $numberOfDiseasedPlayers;
        }

        return 0;
    }

    /**
     * Get the number of incidents that will happen during the cycle.
     * Incident number follows approximately a Poisson distribution P(lambda)
     * where lambda = 3.3*10^(-3) * day^1.7 is the average number of incidents per cycle.
     * During this alpha phase, the number of incidents is multiplied by a constant (currently 4).
     */
    private function getNumberOfIncident(Daedalus $daedalus): int
    {
        $averageIncidentsPerCycle = self::ALPHA_MULTIPLIER * 3.3 * 10 ** (-3) * $daedalus->getDay() ** 1.7;

        return $this->randomService->poissonRandom($averageIncidentsPerCycle);
    }

    /**
     * This function returns the distribution of the working equipment break rate
     * to avoid trying to break a piece of equipment that is already broken
     * (and get less broken equipment than expected).
     */
    private function getWorkingEquipmentBreakRateDistribution(Daedalus $daedalus): ProbaCollection
    {
        $equipmentBreakRateDistribution = $daedalus
            ->getGameConfig()
            ->getDifficultyConfig()
            ->getEquipmentBreakRateDistribution();

        $absentEquipments = [];

        /** @var string $equipmentName */
        foreach ($equipmentBreakRateDistribution as $equipmentName => $probability) {
            // If the equipment is not found, it means it hasn't been build yet (Calculator, Thalasso, etc.)
            // and therefore can't be broken : we skip it.
            try {
                $equipment = $this->gameEquipmentRepository->findByNameAndDaedalus($equipmentName, $daedalus)[0];
                if ($equipment === null || $equipment->isBroken() || $equipment->getPlace()->getType() !== PlaceTypeEnum::ROOM) {
                    $absentEquipments[] = $equipmentName;

                    continue;
                }
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage(), [
                    'equipmentName' => $equipmentName,
                    'daedalus' => $daedalus->getId(),
                    'trace' => $e->getTraceAsString(),
                ]);

                continue;
            }
        }

        return $equipmentBreakRateDistribution->withdrawElements($absentEquipments);
    }
}
