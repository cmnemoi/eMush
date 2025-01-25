<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\ModifyDamageWeaponEffectConfig;

final readonly class ModifyDamageWeaponEffectConfigDto extends WeaponEffectDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public int $quantity = 0,
    ) {}

    public function toEntity(): ModifyDamageWeaponEffectConfig
    {
        return new ModifyDamageWeaponEffectConfig(
            $this->name,
            $this->eventName,
            $this->quantity,
        );
    }
}
