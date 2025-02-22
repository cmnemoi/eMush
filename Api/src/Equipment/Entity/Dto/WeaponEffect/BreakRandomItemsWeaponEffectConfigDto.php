<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\BreakRandomItemsWeaponEffectConfig;

final readonly class BreakRandomItemsWeaponEffectConfigDto extends WeaponEffectDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public int $quantity = 0,
    ) {}

    public function toEntity(): BreakRandomItemsWeaponEffectConfig
    {
        return new BreakRandomItemsWeaponEffectConfig(
            $this->name,
            $this->eventName,
            $this->quantity,
        );
    }
}
