<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\ModifyMaxDamageWeaponEffectConfig;

final readonly class ModifyMaxDamageWeaponEffectConfigDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public int $quantity,
    ) {}

    public function toEntity(): ModifyMaxDamageWeaponEffectConfig
    {
        return new ModifyMaxDamageWeaponEffectConfig(
            $this->name,
            $this->eventName,
            $this->quantity,
        );
    }
}
