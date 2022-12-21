<?php

namespace Mush\Disease\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDisease;

interface ConsumableDiseaseServiceInterface
{
    public function removeAllConsumableDisease(Daedalus $daedalus): void;

    public function findConsumableDiseases(string $name, Daedalus $daedalus): ?ConsumableDisease;

    public function createConsumableDiseases(string $name, Daedalus $daedalus): ?ConsumableDisease;
}
