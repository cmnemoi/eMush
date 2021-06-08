<?php

namespace Mush\Disease\Service;

use Mush\Equipment\Entity\ConsumableEffect;

interface ConsumableDiseaseServiceInterface
{
    public function createConsumableDiseases(string $name, ConsumableEffect $consumableEffect): ?ConsumableEffect;
}
