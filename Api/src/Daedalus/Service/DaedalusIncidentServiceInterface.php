<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;

interface DaedalusIncidentServiceInterface
{
    public function handleFireEvents(Daedalus $daedalus, \DateTime $date): int;

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): int;

    public function handleElectricArcEvents(Daedalus $daedalus, \DateTime $date): int;

    public function handleEquipmentBreak(Daedalus $daedalus, \DateTime $date): int;

    public function handleDoorBreak(Daedalus $daedalus, \DateTime $date): int;

    public function handlePanicCrisis(Daedalus $daedalus, \DateTime $date): int;

    public function handleMetalPlates(Daedalus $daedalus, \DateTime $date): int;

    public function handleCrewDisease(Daedalus $daedalus, \DateTime $date): int;

    public function handleOxygenTankBreak(Daedalus $daedalus, \DateTime $date): int;

    public function handleFuelTankBreak(Daedalus $daedalus, \DateTime $date): int;
}
