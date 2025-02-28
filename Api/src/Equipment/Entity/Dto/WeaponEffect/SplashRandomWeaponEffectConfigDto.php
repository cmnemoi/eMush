<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\SplashRandomWeaponEffectConfig;

final readonly class SplashRandomWeaponEffectConfigDto extends WeaponEffectDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public int $triggerRate = 100,
        public int $quantity = 0,
    ) {}

    public function toEntity(): SplashRandomWeaponEffectConfig
    {
        return new SplashRandomWeaponEffectConfig(
            $this->name,
            $this->eventName,
            $this->triggerRate,
            $this->quantity,
        );
    }
}
