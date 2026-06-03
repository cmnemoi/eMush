<?php

declare(strict_types=1);

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;

interface DifficultyServiceInterface
{
    public function updateDaedalusDifficulty(Daedalus $daedalus, bool $skipIncidentUpdate = false): void;
}
