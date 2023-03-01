<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;

interface CycleServiceInterface
{
    public function handleCycleChange(\DateTime $dateTime, Daedalus $daedalus): int;

    // Temporary function to skip cycle change if it's too long (more than 1 cycle)
    public function handleStuckedDaedalus(Daedalus $daedalus): bool;

    public function getInDayCycleFromDate(\DateTime $date, ClosedDaedalus|Daedalus $daedalus): int;

    public function getDaedalusStartingCycleDate(Daedalus $daedalus): \DateTime;

    public function getDateStartNextCycle(Daedalus $daedalus): \DateTime;
}
