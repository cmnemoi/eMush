<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\DestroyWeaponEffectConfig;

final readonly class DestroyWeaponEffectConfigDto extends WeaponEffectDto
{
    public function toEntity(): DestroyWeaponEffectConfig
    {
        return new DestroyWeaponEffectConfig($this->name, $this->eventName);
    }
}
