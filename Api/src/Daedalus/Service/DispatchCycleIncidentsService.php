<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\CycleIncidentEnum as CycleIncident;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\Random\ProbaCollectionRandomElementServiceInterface;
use Mush\Game\Service\Random\RandomFloatServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\BricBrocProjectWorkedEvent;
use Mush\Status\Enum\PlayerStatusEnum;

final class DispatchCycleIncidentsService
{
    private const INCIDENT_POINTS_THRESHOLD = 18;

    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private DaedalusIncidentServiceInterface $daedalusIncidentService,
        private EventServiceInterface $eventService,
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

        return false;
        // return $this->randomFloat->generateBetween(0, 1) > $daedalus->getIncidentPoints() / self::INCIDENT_POINTS_THRESHOLD;
    }

    private function dispatchIncidents(Daedalus $daedalus, \DateTime $time): void
    {
        while ($daedalus->getIncidentPoints() > 0) {
            $availableIncidents = $this->getAvailableIncidents($daedalus);
            if (empty($availableIncidents)) {
                break;
            }

            $incident = $this->getRandomIncidentToDispatch($availableIncidents);
            dump($incident);
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
        return $this->daedalusCanAffordIncident($daedalus, $incident) && $this->thereIsValidTargetsForIncidentInDaedalus($incident, $daedalus);
    }

    private function buildIncidentWeights(array $availableIncidents): ProbaCollection
    {
        $weights = new ProbaCollection();
        foreach ($availableIncidents as $incident) {
            $weights->setElementProbability($incident->value, $incident->getWeight());
        }

        return $weights;
    }

    private function thereIsValidTargetsForIncidentInDaedalus(CycleIncident $incident, Daedalus $daedalus): bool
    {
        return match ($incident) {
            CycleIncident::FIRE => $daedalus->getRoomsWithoutFire()->count() > 0,
            CycleIncident::OXYGEN_LEAK => \count($this->daedalusIncidentService->getWorkingOxygenTanks($daedalus)) > 0,
            CycleIncident::FUEL_LEAK => \count($this->daedalusIncidentService->getWorkingFuelTanks($daedalus)) > 0,
            CycleIncident::DOOR_BLOCKED => \count($this->daedalusIncidentService->getBreakableDoors($daedalus)) > 0,
            CycleIncident::EQUIPMENT_FAILURE => $this->daedalusIncidentService->getWorkingEquipmentDistribution($daedalus)->count() > 0,
            CycleIncident::ANXIETY_ATTACK, CycleIncident::BOARD_DISEASE => $daedalus->getAlivePlayers()->getHumanPlayer()->count() > 0,
            CycleIncident::ACCIDENT, CycleIncident::JOLT => $daedalus->getAlivePlayers()->getAllInRoom()->getAllExceptWithStatus(PlayerStatusEnum::SELECTED_FOR_STEEL_PLATE)->count() > 0,
            CycleIncident::ELECTROCUTION => true,
            default => throw new \LogicException("Incident type {$incident->value} not found"),
        };
    }

    private function triggerIncidentForDaedalus(CycleIncident $incident, Daedalus $daedalus, \DateTime $time): void
    {
        match ($incident) {
            CycleIncident::FIRE => $this->daedalusIncidentService->handleFireEvents($daedalus, $time),
            CycleIncident::OXYGEN_LEAK => $this->daedalusIncidentService->handleOxygenTankBreak($daedalus, $time),
            CycleIncident::FUEL_LEAK => $this->daedalusIncidentService->handleFuelTankBreak($daedalus, $time),
            CycleIncident::JOLT => $this->daedalusIncidentService->handleTremorEvents($daedalus, $time),
            CycleIncident::EQUIPMENT_FAILURE => $this->daedalusIncidentService->handleEquipmentBreak($daedalus, $time),
            CycleIncident::DOOR_BLOCKED => $this->daedalusIncidentService->handleDoorBreak($daedalus, $time),
            CycleIncident::ANXIETY_ATTACK => $this->daedalusIncidentService->handlePanicCrisis($daedalus, $time),
            CycleIncident::BOARD_DISEASE => $this->daedalusIncidentService->handleCrewDisease($daedalus, $time),
            CycleIncident::ACCIDENT => $this->daedalusIncidentService->handleMetalPlates($daedalus, $time),
            CycleIncident::ELECTROCUTION => $this->daedalusIncidentService->handleElectricArcEvents($daedalus, $time),
            default => throw new \LogicException("Incident type {$incident->value} not found"),
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
}
