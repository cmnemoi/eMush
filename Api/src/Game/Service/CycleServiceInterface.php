<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Exploration;

interface CycleServiceInterface
{
    public function handleCycleChange(\DateTime $dateTime, Daedalus $daedalus): int;

    public function getInDayCycleFromDate(\DateTime $date, ClosedDaedalus|Daedalus $daedalus): int;

    public function getDaedalusStartingCycleDate(Daedalus $daedalus): \DateTime;

    public function getDateStartNextCycle(Daedalus $daedalus): \DateTime;

    public function getNumberOfCycleElapsed(\DateTime $start, \DateTime $end, Daedalus $daedalus): int;

    public function handleExplorationCycleChange(\DateTime $dateTime, Exploration $exploration): int;

    public function getExplorationDateStartNextCycle(Exploration $exploration): \DateTime;
}
