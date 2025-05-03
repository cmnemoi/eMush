<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\CycleIncidentEnum as CycleIncident;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\Random\ProbaCollectionRandomElementServiceInterface;
use Mush\Game\Service\Random\RandomFloatServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\BricBrocProjectWorkedEvent;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

final class DispatchCycleIncidentsService
{
    private const INCIDENT_POINTS_THRESHOLD = 18;

    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private DaedalusIncidentServiceInterface $daedalusIncidentService,
        private EventServiceInterface $eventService,
        private GameEquipmentRepositoryInterface $gameEquipmentRepository,
        private ProbaCollectionRandomElementServiceInterface $probaCollectionRandomElement,
        private RandomFloatServiceInterface $randomFloat,
    ) {}

    public function execute(Daedalus $daedalus, \DateTime $time): bool
    {
        if ($this->isPrevented($daedalus, $time)) {
            return false;
        }

        $this->dispatchIncidents($daedalus, $time);

        return true;
    }

    private function isPrevented(Daedalus $daedalus, \DateTime $time): bool
    {
        if ($daedalus->isFilling() || $this->isPreventedByBricBroc($daedalus, $time)) {
            return true;
        }

        return $this->randomFloat->generateBetween(0, 1) > $daedalus->getIncidentPoints() / self::INCIDENT_POINTS_THRESHOLD;
    }

    private function dispatchIncidents(Daedalus $daedalus, \DateTime $time): void
    {
        while ($daedalus->getIncidentPoints() > 0) {
            $availableIncidents = $this->getAvailableIncidents($daedalus);
            if (empty($availableIncidents)) {
                break;
            }

            $incident = $this->getRandomIncidentToDispatch($availableIncidents);
            $this->triggerIncidentForDaedalus($incident, $daedalus, $time);
            $daedalus->removeIncidentPoints($incident->getCost());
        }
    }

    /**
     * @return CycleIncident[]
     */
    private function getAvailableIncidents(Daedalus $daedalus): array
    {
        $availableIncidents = [];

        foreach (CycleIncident::cases() as $incident) {
            if ($this->incidentAvailableForDaedalus($incident, $daedalus)) {
                $availableIncidents[] = $incident;
            }
        }

        return $availableIncidents;
    }

    private function getRandomIncidentToDispatch(array $availableIncidents): CycleIncident
    {
        $weights = $this->buildIncidentWeights($availableIncidents);

        return CycleIncident::from($this->probaCollectionRandomElement->generateFrom($weights));
    }

    private function incidentAvailableForDaedalus(CycleIncident $incident, Daedalus $daedalus): bool
    {
        return $this->daedalusCanAffordIncident($daedalus, $incident) && $this->thereAreValidTargetsForIncidentInDaedalus($incident, $daedalus);
    }

    private function buildIncidentWeights(array $availableIncidents): ProbaCollection
    {
        $weights = new ProbaCollection();
        foreach ($availableIncidents as $incident) {
            $weights->setElementProbability($incident->value, $incident->getWeight());
        }

        return $weights;
    }

    private function thereAreValidTargetsForIncidentInDaedalus(CycleIncident $incident, Daedalus $daedalus): bool
    {
        return match ($incident->getTarget()) {
            Place::class => \count($this->getValidRoomsForIncident($daedalus, $incident)) > 0,
            GameEquipment::class => \count($this->getValidEquipmentForIncident($daedalus, $incident)) > 0,
            Player::class => \count($this->getValidPlayersForIncident($daedalus, $incident)) > 0,
            'equipment_failure' => $this->getWorkingEquipmentDistribution($daedalus)->count() > 0,
            default => throw new \LogicException("Incident type {$incident->value} not supported"),
        };
    }

    private function triggerIncidentForDaedalus(CycleIncident $incident, Daedalus $daedalus, \DateTime $time): void
    {
        match ($incident) {
            CycleIncident::FIRE => $this->daedalusIncidentService->handleFireEvents(
                rooms: $this->getValidRoomsForIncident($daedalus, $incident),
                date: $time,
            ),
            CycleIncident::OXYGEN_LEAK => $this->daedalusIncidentService->handleOxygenTankBreak(
                tanks: $this->getValidEquipmentForIncident($daedalus, $incident),
                date: $time,
            ),
            CycleIncident::FUEL_LEAK => $this->daedalusIncidentService->handleFuelTankBreak(
                tanks: $this->getValidEquipmentForIncident($daedalus, $incident),
                date: $time,
            ),
            CycleIncident::JOLT => $this->daedalusIncidentService->handleTremorEvents(
                rooms: $this->getValidRoomsForIncident($daedalus, $incident),
                date: $time,
            ),
            CycleIncident::EQUIPMENT_FAILURE => $this->daedalusIncidentService->handleEquipmentBreak(
                equipments: $this->getWorkingEquipmentDistribution($daedalus),
                daedalus: $daedalus,
                date: $time,
            ),
            CycleIncident::DOOR_BLOCKED => $this->daedalusIncidentService->handleDoorBreak(
                doors: $this->getBreakableDoorsFromDaedalus($daedalus),
                date: $time,
            ),
            CycleIncident::ANXIETY_ATTACK => $this->daedalusIncidentService->handlePanicCrisis(
                players: $this->getValidPlayersForIncident($daedalus, $incident),
                date: $time,
            ),
            CycleIncident::BOARD_DISEASE => $this->daedalusIncidentService->handleCrewDisease(
                players: $this->getValidPlayersForIncident($daedalus, $incident),
                date: $time,
            ),
            CycleIncident::ACCIDENT => $this->daedalusIncidentService->handleMetalPlates(
                players: $this->getValidPlayersForIncident($daedalus, $incident),
                date: $time,
            ),
            CycleIncident::ELECTROCUTION => $this->daedalusIncidentService->handleElectricArcEvents(
                rooms: $this->getValidRoomsForIncident($daedalus, $incident),
                date: $time,
            ),
            default => throw new \LogicException("Incident type {$incident->value} not supported"),
        };
    }

    private function isPreventedByBricBroc(Daedalus $daedalus, \DateTime $time): bool
    {
        $bricBroc = $daedalus->getProjectByName(ProjectName::BRIC_BROC);
        if (!$bricBroc->isFinished() || $this->d100Roll->isAFailure($bricBroc->getActivationRate())) {
            return false;
        }

        $bricBrocWorkedEvent = new BricBrocProjectWorkedEvent($daedalus, [EventEnum::NEW_CYCLE], $time);
        $this->eventService->callEvent($bricBrocWorkedEvent, BricBrocProjectWorkedEvent::class);

        return true;
    }

    private function daedalusCanAffordIncident(Daedalus $daedalus, CycleIncident $incident): bool
    {
        return $daedalus->getIncidentPoints() >= $incident->getCost();
    }

    private function getValidPlayersForIncident(Daedalus $daedalus, CycleIncident $incident): array
    {
        return match ($incident) {
            CycleIncident::ACCIDENT => $daedalus->getAlivePlayers()->getAllInRoom()->getAllWithoutStatus(PlayerStatusEnum::SELECTED_FOR_STEEL_PLATE)->toArray(),
            CycleIncident::ANXIETY_ATTACK => $daedalus->getAlivePlayers()->getHumanPlayer()->toArray(),
            CycleIncident::BOARD_DISEASE => $daedalus->getAlivePlayers()->getHumanPlayer()->getAllWithoutStatus(PlayerStatusEnum::SELECTED_FOR_BOARD_DISEASE)->toArray(),
            default => throw new \LogicException("Incident type {$incident->value} not supported"),
        };
    }

    private function getValidRoomsForIncident(Daedalus $daedalus, CycleIncident $incident): array
    {
        return match ($incident) {
            CycleIncident::ELECTROCUTION => $daedalus->getRooms()->getAllWithoutStatus(PlaceStatusEnum::SELECTED_FOR_ELECTROCUTION->toString())->toArray(),
            CycleIncident::FIRE => $daedalus->getRooms()->getAllWithoutStatus(StatusEnum::FIRE)->toArray(),
            CycleIncident::JOLT => $daedalus->getRooms()->getAllWithAlivePlayers()->getAllWithoutStatus(PlaceStatusEnum::SELECTED_FOR_JOLT->toString())->toArray(),
            default => throw new \LogicException("Incident type {$incident->value} not supported"),
        };
    }

    private function getValidEquipmentForIncident(Daedalus $daedalus, CycleIncident $incident): array
    {
        return match ($incident) {
            CycleIncident::DOOR_BLOCKED => $this->getBreakableDoorsFromDaedalus($daedalus),
            CycleIncident::FUEL_LEAK => $this->getAllWorkingDaedalusEquipmentByName($daedalus, EquipmentEnum::FUEL_TANK),
            CycleIncident::OXYGEN_LEAK => $this->getAllWorkingDaedalusEquipmentByName($daedalus, EquipmentEnum::OXYGEN_TANK),
            default => throw new \LogicException("Incident type {$incident->value} not supported"),
        };
    }

    private function getAllWorkingDaedalusEquipmentByName(Daedalus $daedalus, string $equipmentName): array
    {
        $tanks = $this->gameEquipmentRepository->findByNameAndDaedalus($equipmentName, $daedalus);

        return array_filter($tanks, static fn (GameEquipment $tank) => !$tank->isBroken());
    }

    private function getWorkingEquipmentDistribution(Daedalus $daedalus): ProbaCollection
    {
        $equipmentBreakRateDistribution = $daedalus
            ->getGameConfig()
            ->getDifficultyConfig()
            ->getEquipmentBreakRateDistribution();

        $absentEquipments = [];

        /** @var string $equipmentName */
        foreach ($equipmentBreakRateDistribution as $equipmentName => $probability) {
            /** @var array<int, GameEquipment> $equipments */
            $equipments = $this->gameEquipmentRepository->findByNameAndDaedalus($equipmentName, $daedalus);
            // first, remove equipment which not present on the daedalus
            if (empty($equipments)) {
                $absentEquipments[] = $equipmentName;

                continue;
            }

            // then, remove equipment which is already broken or patrol ships in space battle
            foreach ($equipments as $equipment) {
                if ($equipment->isBroken() || $equipment->isInSpaceBattle()) {
                    $absentEquipments[] = $equipmentName;
                }
            }
        }

        return $equipmentBreakRateDistribution->withdrawElements($absentEquipments);
    }

    private function getBreakableDoorsFromDaedalus(Daedalus $daedalus): array
    {
        $criteria = new GameEquipmentCriteria($daedalus);
        $criteria->setInstanceOf([Door::class]);

        $daedalusDoors = $this->gameEquipmentRepository->findByCriteria($criteria);

        return array_filter($daedalusDoors, static fn (Door $door) => $door->isBreakable() && !$door->isBroken());
    }
}
