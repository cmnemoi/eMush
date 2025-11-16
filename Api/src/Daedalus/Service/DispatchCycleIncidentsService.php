<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Collection\CycleIncidentCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\CycleIncidentEnum;
use Mush\Daedalus\ValueObject\CycleIncident;
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
            $incidents = $this->getAvailableIncidentsForDaedalus($daedalus);
            if ($incidents->isEmpty()) {
                break;
            }

            $incident = $this->getRandomIncidentToDispatch($incidents);
            $this->triggerIncidentForDaedalus($incident, $daedalus, $time);
            $daedalus->removeIncidentPoints($incident->cost);
        }
    }

    private function getAvailableIncidentsForDaedalus(Daedalus $daedalus): CycleIncidentCollection
    {
        $incidents = new CycleIncidentCollection();
        foreach (CycleIncidentEnum::cases() as $incidentName) {
            if ($this->daedalusCannotAffordIncident($daedalus, $incidentName)) {
                continue;
            }

            $targets = match ($incidentName->getTarget()) {
                Place::class => $this->getValidDaedalusRoomsForIncident($daedalus, $incidentName),
                GameEquipment::class => $this->getValidDaedalusEquipmentForIncident($daedalus, $incidentName),
                Player::class => $this->getValidDaedalusPlayersForIncident($daedalus, $incidentName),
                'random_equipment' => $this->getWorkingEquipmentDistributionFromDaedalus($daedalus)->toArray(),
                default => throw new \LogicException("Incident type {$incidentName->value} not supported"),
            };

            if (\count($targets) > 0) {
                $incidents[] = new CycleIncident($incidentName, $targets);
            }
        }

        return $incidents;
    }

    private function getRandomIncidentToDispatch(CycleIncidentCollection $incidents): CycleIncident
    {
        $weights = $incidents->getWeights();
        $selectedIncident = CycleIncidentEnum::from($this->probaCollectionRandomElement->generateFrom($weights));

        return $incidents->getByNameOrThrow($selectedIncident);
    }

    private function triggerIncidentForDaedalus(CycleIncident $incident, Daedalus $daedalus, \DateTime $time): void
    {
        $targets = $incident->targets;

        match ($incident->name) {
            CycleIncidentEnum::FIRE => $this->daedalusIncidentService->handleFireEvents(
                rooms: $targets,
                date: $time,
            ),
            CycleIncidentEnum::OXYGEN_LEAK => $this->daedalusIncidentService->handleOxygenTankBreak(
                tanks: $targets,
                date: $time,
            ),
            CycleIncidentEnum::FUEL_LEAK => $this->daedalusIncidentService->handleFuelTankBreak(
                tanks: $targets,
                date: $time,
            ),
            CycleIncidentEnum::JOLT => $this->daedalusIncidentService->handleTremorEvents(
                rooms: $targets,
                date: $time,
            ),
            CycleIncidentEnum::EQUIPMENT_FAILURE => $this->daedalusIncidentService->handleEquipmentBreak(
                equipments: new ProbaCollection($targets),
                daedalus: $daedalus,
                date: $time,
            ),
            CycleIncidentEnum::DOOR_BLOCKED => $this->daedalusIncidentService->handleDoorBreak(
                doors: $targets,
                date: $time,
            ),
            CycleIncidentEnum::ANXIETY_ATTACK => $this->daedalusIncidentService->handlePanicCrisis(
                players: $targets,
                date: $time,
            ),
            CycleIncidentEnum::BOARD_DISEASE => $this->daedalusIncidentService->handleCrewDisease(
                players: $targets,
                date: $time,
            ),
            CycleIncidentEnum::ACCIDENT => $this->daedalusIncidentService->handleMetalPlates(
                players: $targets,
                date: $time,
            ),
            CycleIncidentEnum::ELECTROCUTION => $this->daedalusIncidentService->handleElectricArcEvents(
                rooms: $targets,
                date: $time,
            ),
            default => throw new \LogicException("Incident type {$incident->name->value} not supported"),
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

    private function daedalusCannotAffordIncident(Daedalus $daedalus, CycleIncidentEnum $incident): bool
    {
        return $daedalus->getIncidentPoints() < $incident->getCost();
    }

    /**
     * @return Player[]
     */
    private function getValidDaedalusPlayersForIncident(Daedalus $daedalus, CycleIncidentEnum $incidentName): array
    {
        return match ($incidentName) {
            CycleIncidentEnum::ACCIDENT => $daedalus->getAlivePlayers()->getAllInRoom()->getAllWithoutStatus(PlayerStatusEnum::SELECTED_FOR_STEEL_PLATE)->toArray(),
            CycleIncidentEnum::ANXIETY_ATTACK => $daedalus->getAlivePlayers()->getHumanPlayer()->getAllWithoutStatus(PlayerStatusEnum::SELECTED_FOR_ANXIETY_ATTACK)->toArray(),
            CycleIncidentEnum::BOARD_DISEASE => $daedalus->getAlivePlayers()->getHumanPlayer()->getAllWithoutStatus(PlayerStatusEnum::SELECTED_FOR_BOARD_DISEASE)->toArray(),
            default => throw new \LogicException("Incident type {$incidentName->toString()} not supported"),
        };
    }

    /**
     * @return Place[]
     */
    private function getValidDaedalusRoomsForIncident(Daedalus $daedalus, CycleIncidentEnum $incidentName): array
    {
        return match ($incidentName) {
            CycleIncidentEnum::ELECTROCUTION => $daedalus->getRooms()->getAllWithoutStatus(PlaceStatusEnum::SELECTED_FOR_ELECTROCUTION->toString())->toArray(),
            CycleIncidentEnum::FIRE => $daedalus->getRooms()->getAllWithoutStatus(StatusEnum::FIRE)->getAllWithoutStatus(PlaceStatusEnum::SELECTED_FOR_FIRE->toString())->toArray(),
            CycleIncidentEnum::JOLT => $daedalus->getRooms()->getAllWithAlivePlayers()->getAllWithoutStatus(PlaceStatusEnum::SELECTED_FOR_JOLT->toString())->toArray(),
            default => throw new \LogicException("Incident type {$incidentName->toString()} not supported"),
        };
    }

    /**
     * @return GameEquipment[]
     */
    private function getValidDaedalusEquipmentForIncident(Daedalus $daedalus, CycleIncidentEnum $incidentName): array
    {
        return match ($incidentName) {
            CycleIncidentEnum::DOOR_BLOCKED => $this->getBreakableDoorsFromDaedalus($daedalus),
            CycleIncidentEnum::FUEL_LEAK => $this->getAllWorkingDaedalusEquipmentByName($daedalus, EquipmentEnum::FUEL_TANK),
            CycleIncidentEnum::OXYGEN_LEAK => $this->getAllWorkingDaedalusEquipmentByName($daedalus, EquipmentEnum::OXYGEN_TANK),
            default => throw new \LogicException("Incident type {$incidentName->toString()} not supported"),
        };
    }

    /**
     * @return GameEquipment[]
     */
    private function getAllWorkingDaedalusEquipmentByName(Daedalus $daedalus, string $equipmentName): array
    {
        $tanks = $this->gameEquipmentRepository->findByNameAndDaedalus($equipmentName, $daedalus);

        return array_filter($tanks, static fn (GameEquipment $tank) => !$tank->isBroken());
    }

    private function getWorkingEquipmentDistributionFromDaedalus(Daedalus $daedalus): ProbaCollection
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

    /**
     * @return Door[]
     */
    private function getBreakableDoorsFromDaedalus(Daedalus $daedalus): array
    {
        $criteria = new GameEquipmentCriteria($daedalus);
        $criteria->setInstanceOf([Door::class]);

        $daedalusDoors = $this->gameEquipmentRepository->findByCriteria($criteria);

        return array_filter($daedalusDoors, static fn (Door $door) => $door->isBreakable() && !$door->isBroken());
    }
}
