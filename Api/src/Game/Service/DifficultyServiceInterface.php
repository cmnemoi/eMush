<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;

interface DifficultyServiceInterface
{
    public function updateDaedalusDifficulty(Daedalus $daedalus): void;
}
