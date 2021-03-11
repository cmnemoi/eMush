<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;

interface CycleServiceInterface
{
    public function handleCycleChange(\DateTime $dateTime, Daedalus $daedalus): int;

    public function getInDayCycleFromDate(\DateTime $date, GameConfig $gameConfig): int;

    public function getDaedalusStartingCycleDate(Daedalus $daedalus): \DateTime;

    public function getDateStartNextCycle(Daedalus $daedalus): \DateTime;
}
