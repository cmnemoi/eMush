<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\DestroyOrBreakRandomItemsWeaponEffectConfig;

final readonly class DestroyOrBreakRandomItemsWeaponEffectConfigDto extends WeaponEffectDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public int $quantity = 0,
    ) {}

    public function toEntity(): DestroyOrBreakRandomItemsWeaponEffectConfig
    {
        return new DestroyOrBreakRandomItemsWeaponEffectConfig(
            $this->name,
            $this->eventName,
            $this->quantity,
        );
    }
}
