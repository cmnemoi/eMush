<?php

namespace Mush\Equipment\Repository;

use Mush\Equipment\Entity\Config\WeaponEventConfig;

interface WeaponEffectConfigRepositoryInterface
{
    public function findAllByWeaponEvent(WeaponEventConfig $weaponEvent): array;
}
