<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;

interface CycleServiceInterface
{
    public function handleCycleChange(\DateTime $dateTime, Daedalus $daedalus): int;

    /**
     * Temporary debug function to skip cycle change if Daedalus is stucked in it for too long (2 cycles).
     *
     * @return bool : true if cycle change was skipped
     */
    public function handleStuckedDaedalus(Daedalus $daedalus): bool;

    public function getInDayCycleFromDate(\DateTime $date, ClosedDaedalus|Daedalus $daedalus): int;

    public function getDaedalusStartingCycleDate(Daedalus $daedalus): \DateTime;

    public function getDateStartNextCycle(Daedalus $daedalus): \DateTime;
}
