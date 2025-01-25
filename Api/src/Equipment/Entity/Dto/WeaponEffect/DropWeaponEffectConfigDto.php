<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\DropWeaponEffectConfig;

final readonly class DropWeaponEffectConfigDto extends WeaponEffectDto
{
    public function toEntity(): DropWeaponEffectConfig
    {
        return new DropWeaponEffectConfig($this->name, $this->eventName);
    }
}
