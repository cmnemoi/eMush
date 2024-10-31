<?php

declare(strict_types=1);

namespace Mush\Equipment\Repository;

use Mush\Equipment\Entity\Config\WeaponEventConfig;

interface WeaponEventConfigRepositoryInterface
{
    public function findOneByKey(string $eventKey): WeaponEventConfig;
}
