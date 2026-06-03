<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

interface RandomWeaponEffectConfig
{
    public function getTriggerRate(): int;
}
