<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

interface BackfireWeaponEffectConfig
{
    public function applyToShooter(): bool;
}
