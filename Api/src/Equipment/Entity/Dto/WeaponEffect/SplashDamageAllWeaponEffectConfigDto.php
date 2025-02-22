<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\SplashDamageAllWeaponEffectConfig;

final readonly class SplashDamageAllWeaponEffectConfigDto extends WeaponEffectDto
{
    public function __construct(
        public string $name,
        public string $eventName,
    ) {}

    public function toEntity(): SplashDamageAllWeaponEffectConfig
    {
        return new SplashDamageAllWeaponEffectConfig(
            $this->name,
            $this->eventName,
        );
    }
}
