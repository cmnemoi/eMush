<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\InflictInjuryWeaponEffectConfig;

final readonly class InflictInjuryWeaponEffectConfigDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public string $injuryName,
        public int $triggerRate = 100,
        public bool $toShooter = false,
    ) {}

    public function toEntity(): InflictInjuryWeaponEffectConfig
    {
        return new InflictInjuryWeaponEffectConfig(
            $this->name,
            $this->eventName,
            $this->injuryName,
            $this->triggerRate,
            $this->toShooter,
        );
    }
}
