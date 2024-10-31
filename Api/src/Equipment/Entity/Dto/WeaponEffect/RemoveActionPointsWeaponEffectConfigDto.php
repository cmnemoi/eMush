<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\RemoveActionPointsWeaponEffectConfig;

final readonly class RemoveActionPointsWeaponEffectConfigDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public int $quantity,
        public bool $toShooter = false,
    ) {}

    public function toEntity(): RemoveActionPointsWeaponEffectConfig
    {
        return new RemoveActionPointsWeaponEffectConfig(
            $this->name,
            $this->eventName,
            $this->quantity,
            $this->toShooter,
        );
    }
}
