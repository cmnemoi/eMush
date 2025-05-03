<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\Collection\ProbaCollection;

interface DaedalusIncidentServiceInterface
{
    public function handleFireEvents(array $rooms, \DateTime $date): void;

    public function handleTremorEvents(array $rooms, \DateTime $date): void;

    public function handleElectricArcEvents(array $rooms, \DateTime $date): void;

    public function handleEquipmentBreak(ProbaCollection $equipments, Daedalus $daedalus, \DateTime $date): void;

    public function handleDoorBreak(array $doors, \DateTime $date): void;

    public function handlePanicCrisis(array $players, \DateTime $date): void;

    public function handleMetalPlates(array $players, \DateTime $date): void;

    public function handleCrewDisease(array $players, \DateTime $date): void;

    public function handleOxygenTankBreak(array $tanks, \DateTime $date): void;

    public function handleFuelTankBreak(array $tanks, \DateTime $date): void;
}
