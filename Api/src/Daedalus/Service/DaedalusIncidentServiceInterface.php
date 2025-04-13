<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\Collection\ProbaCollection;

interface DaedalusIncidentServiceInterface
{
    public function handleFireEvents(Daedalus $daedalus, \DateTime $date): void;

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): void;

    public function handleElectricArcEvents(Daedalus $daedalus, \DateTime $date): void;

    public function handleEquipmentBreak(Daedalus $daedalus, \DateTime $date): void;

    public function handleDoorBreak(Daedalus $daedalus, \DateTime $date): void;

    public function handlePanicCrisis(Daedalus $daedalus, \DateTime $date): void;

    public function handleMetalPlates(Daedalus $daedalus, \DateTime $date): void;

    public function handleCrewDisease(Daedalus $daedalus, \DateTime $date): void;

    public function handleOxygenTankBreak(Daedalus $daedalus, \DateTime $date): void;

    public function handleFuelTankBreak(Daedalus $daedalus, \DateTime $date): void;

    public function getWorkingOxygenTanks(Daedalus $daedalus): array;

    public function getWorkingFuelTanks(Daedalus $daedalus): array;

    public function getWorkingEquipmentDistribution(Daedalus $daedalus): ProbaCollection;

    public function getBreakableDoors(Daedalus $daedalus): array;
}
