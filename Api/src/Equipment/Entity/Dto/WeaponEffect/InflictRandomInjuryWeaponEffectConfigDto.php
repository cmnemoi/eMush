<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\InflictRandomInjuryWeaponEffectConfig;

final readonly class InflictRandomInjuryWeaponEffectConfigDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public bool $toShooter = false,
    ) {}

    public function toEntity(): InflictRandomInjuryWeaponEffectConfig
    {
        return new InflictRandomInjuryWeaponEffectConfig(
            $this->name,
            $this->eventName,
            $this->toShooter,
        );
    }
}
