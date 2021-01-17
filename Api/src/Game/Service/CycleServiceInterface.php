<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;

interface CycleServiceInterface
{
    public function handleCycleChange(Daedalus $daedalus): int;

    public function getCycleFromDate(\DateTime $date, GameConfig $gameConfig): int;

    public function getDateStartNextCycle(Daedalus $daedalus): \DateTime;
}
