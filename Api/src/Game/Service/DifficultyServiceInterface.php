<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;

interface DifficultyServiceInterface
{
    public function updateDaedalusDifficultyPoints(Daedalus $daedalus, string $pointsType): void;
}
