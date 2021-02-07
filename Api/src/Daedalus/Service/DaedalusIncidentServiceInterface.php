<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;

interface DaedalusIncidentServiceInterface
{
    public function handleFireEvents(Daedalus $daedalus, \DateTime $date): int;

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): int;

    public function handleElectricArcEvents(Daedalus $daedalus, \DateTime $date): int;
}
