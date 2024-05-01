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
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Game\Service\Random\GetRandomPoissonIntegerServiceInterface;
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

final class DaedalusIncidentService implements DaedalusIncidentServiceInterface
{
    private const ALPHA_MULTIPLIER = 3;

    private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray;
    private GetRandomPoissonIntegerServiceInterface $getRandomPoissonInteger;
    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;
    private GameEquipmentRepository $gameEquipmentRepository;
    private StatusServiceInterface $statusService;
    private LoggerInterface $logger;

    public function __construct(
        GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
        GetRandomPoissonIntegerServiceInterface $getRandomPoissonInteger,
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService,
        GameEquipmentRepository $gameEquipmentRepository,
        StatusServiceInterface $statusService,
        LoggerInterface $logger,
    ) {
        $this->getRandomElementsFromArray = $getRandomElementsFromArray;
        $this->getRandomPoissonInteger = $getRandomPoissonInteger;
        $this->randomService = $randomService;
        $this->eventService = $eventService;
        $this->gameEquipmentRepository = $gameEquipmentRepository;
        $this->logger = $logger;
        $this->statusService = $statusService;
    }

    public function handleFireEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $newFireRooms = $this->getRandomElementsFromArray->execute(
            elements: $daedalus->getRoomsWithoutFire()->toArray(),
            number: $this->getNumberOfIncident($daedalus)
        );

        /** @var Place $room */
        foreach ($newFireRooms as $room) {
            $this->statusService->createStatusFromName(
                StatusEnum::FIRE,
                $room,
                [EventEnum::NEW_CYCLE, StatusEnum::FIRE],
                $date
            );
        }

        return \count($newFireRooms);
    }

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $rooms = $daedalus->getRoomsWithAlivePlayers();
        $newTremorRooms = $this->getRandomElementsFromArray->execute($rooms->toArray(), $this->getNumberOfIncident($daedalus));

        /** @var Place $room */
        foreach ($newTremorRooms as $room) {
            $roomEvent = new RoomEvent(
                $room,
                [EventEnum::NEW_CYCLE, RoomEvent::TREMOR],
                $date
            );
            $this->eventService->callEvent($roomEvent, RoomEvent::TREMOR);
        }

        return \count($newTremorRooms);
    }

    public function handleElectricArcEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $rooms = $daedalus->getRooms();
        $newElectricArcs = $this->getRandomElementsFromArray->execute($rooms->toArray(), $this->getNumberOfIncident($daedalus));

        /** @var Place $room */
        foreach ($newElectricArcs as $room) {
            $roomEvent = new RoomEvent(
                $room,
                [EventEnum::NEW_CYCLE, RoomEvent::ELECTRIC_ARC],
                $date
            );
            $this->eventService->callEvent($roomEvent, RoomEvent::ELECTRIC_ARC);
        }

        return \count($newElectricArcs);
    }

    public function handleEquipmentBreak(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfEquipmentToBreak = $this->getNumberOfIncident($daedalus);

        $workingEquipmentBreakRateDistribution = $this->getWorkingEquipmentBreakRateDistribution($daedalus);

        // If there is no working equipment, we don't have to break anything, so we return 0
        if ($workingEquipmentBreakRateDistribution->isEmpty()) {
            return 0;
        }

        // If there is less working equipment than the number of equipment we want to break
        // we break the number of working equipment instead to avoid an error
        $numberOfEquipmentBroken = min($numberOfEquipmentToBreak, $workingEquipmentBreakRateDistribution->count());
        $equipmentToBreak = $this
            ->randomService
            ->getRandomDaedalusEquipmentFromProbaCollection(
                $workingEquipmentBreakRateDistribution,
                $numberOfEquipmentBroken,
                $daedalus
            );

        foreach ($equipmentToBreak as $gameEquipment) {
            $this->statusService->createStatusFromName(
                EquipmentStatusEnum::BROKEN,
                $gameEquipment,
                [EventEnum::NEW_CYCLE, EquipmentEvent::EQUIPMENT_BROKEN],
                new \DateTime()
            );
        }

        return $numberOfEquipmentBroken;
    }

    public function handleDoorBreak(Daedalus $daedalus, \DateTime $date): int
    {
        $breakableDoors = $this->getBreakableDoors($daedalus);

        $doorsToBreak = $this->getRandomElementsFromArray->execute(
            elements: $breakableDoors,
            number: $this->getNumberOfIncident($daedalus)
        );

        foreach ($doorsToBreak as $door) {
            $this->statusService->createStatusFromName(
                EquipmentStatusEnum::BROKEN,
                $door,
                [EventEnum::NEW_CYCLE, EquipmentEvent::DOOR_BROKEN],
                new \DateTime()
            );
        }

        return \count($doorsToBreak);
    }

    public function handlePanicCrisis(Daedalus $daedalus, \DateTime $date): int
    {
        // If there is no human player alive, no panic crisis can happen
        $humanPlayers = $daedalus->getPlayers()->getPlayerAlive()->getHumanPlayer();

        $humansCrisis = $this->getRandomElementsFromArray->execute(
            elements: $humanPlayers->toArray(),
            number: $this->getNumberOfIncident($daedalus)
        );

        foreach ($humansCrisis as $player) {
            $playerEvent = new PlayerEvent(
                $player,
                [EventEnum::NEW_CYCLE, PlayerEvent::PANIC_CRISIS],
                $date
            );
            $this->eventService->callEvent($playerEvent, PlayerEvent::PANIC_CRISIS);
        }

        return \count($humansCrisis);
    }

    public function handleMetalPlates(Daedalus $daedalus, \DateTime $date): int
    {
        $alivePlayers = $daedalus->getPlayers()->getPlayerAlive();

        $metalPlatesPlayers = $this->getRandomElementsFromArray->execute(
            elements: $alivePlayers->toArray(),
            number: $this->getNumberOfIncident($daedalus)
        );

        foreach ($metalPlatesPlayers as $player) {
            $playerEvent = new PlayerEvent(
                $player,
                [EventEnum::NEW_CYCLE, PlayerEvent::METAL_PLATE],
                $date
            );
            $this->eventService->callEvent($playerEvent, PlayerEvent::METAL_PLATE);
        }

        return \count($metalPlatesPlayers);
    }

    public function handleCrewDisease(Daedalus $daedalus, \DateTime $date): int
    {
        $humanAlivePlayers = $daedalus->getPlayers()->getPlayerAlive()->getHumanPlayer();

        $numberOfDiseasedPlayers = $this->getNumberOfIncident($daedalus);

        $diseasedPlayers = $this->getRandomElementsFromArray->execute($humanAlivePlayers->toArray(), $numberOfDiseasedPlayers);

        foreach ($diseasedPlayers as $player) {
            $playerEvent = new PlayerEvent(
                $player,
                [EventEnum::NEW_CYCLE, PlayerEvent::CYCLE_DISEASE],
                $date
            );
            $this->eventService->callEvent($playerEvent, PlayerEvent::CYCLE_DISEASE);
        }

        return $numberOfDiseasedPlayers;
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

        return $this->getRandomPoissonInteger->execute($averageIncidentsPerCycle);
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

    private function getBreakableDoors(Daedalus $daedalus): array
    {
        $criteria = new GameEquipmentCriteria($daedalus);
        $criteria->setInstanceOf([Door::class]);

        $daedalusDoors = $this->gameEquipmentRepository->findByCriteria($criteria);

        $daedalusDoorsNames = array_map(static fn (Door $door) => $door->getName(), $daedalusDoors);

        $breakableDoorsNames = array_filter($daedalusDoorsNames, static fn (string $doorName) => DoorEnum::isBreakable($doorName));

        $breakableDoors = array_filter($daedalusDoors, static fn (Door $door) => \in_array($door->getName(), $breakableDoorsNames, true));

        $breakableDoors = array_filter($breakableDoors, static fn (Door $door) => !$door->isBroken());

        return $breakableDoors;
    }
}
