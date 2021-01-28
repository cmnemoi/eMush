<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;

interface CycleServiceInterface
{
    public function handleCycleChange(\DateTime $dateTime, Daedalus $daedalus): int;

    public function getCycleFromDate(\DateTime $date, Daedalus $daedalus): int;

    public function getDayFromDate(\DateTime $date, Daedalus $daedalus): int;

    public function getDateStartNextCycle(Daedalus $daedalus): \DateTime;
}
