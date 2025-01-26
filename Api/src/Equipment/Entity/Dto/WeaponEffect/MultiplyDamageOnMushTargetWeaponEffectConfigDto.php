<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\MultiplyDamageOnMushTargetWeaponEffectConfig;

final readonly class MultiplyDamageOnMushTargetWeaponEffectConfigDto extends WeaponEffectDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public int $quantity,
    ) {}

    public function toEntity(): MultiplyDamageOnMushTargetWeaponEffectConfig
    {
        return new MultiplyDamageOnMushTargetWeaponEffectConfig(
            $this->name,
            $this->eventName,
            $this->quantity,
        );
    }
}
