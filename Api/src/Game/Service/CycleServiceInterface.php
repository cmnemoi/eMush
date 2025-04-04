<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Exploration\Entity\Exploration;
use Mush\Game\ValueObject\CycleChangeResult;

interface CycleServiceInterface
{
    public function handleDaedalusAndExplorationCycleChanges(\DateTime $dateTime, Daedalus $daedalus): CycleChangeResult;

    public function getInDayCycleFromDate(\DateTime $date, ClosedDaedalus|Daedalus $daedalus): int;

    public function getDaedalusStartingCycleDate(Daedalus $daedalus): \DateTime;

    public function getDateStartNextCycle(Daedalus $daedalus): \DateTime;

    public function getNumberOfCycleElapsed(\DateTime $start, \DateTime $end, DaedalusInfo $daedalusInfo): int;

    public function getExplorationDateStartNextCycle(Exploration $exploration): \DateTime;

    public function handleExplorationCycleChange(\DateTime $dateTime, Exploration $exploration): int;
}
