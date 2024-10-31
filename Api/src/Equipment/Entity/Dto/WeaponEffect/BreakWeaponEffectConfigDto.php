<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\BreakWeaponEffectConfig;

final readonly class BreakWeaponEffectConfigDto
{
    public function __construct(
        public string $name,
        public string $eventName,
    ) {}

    public function toEntity(): BreakWeaponEffectConfig
    {
        return new BreakWeaponEffectConfig($this->name, $this->eventName);
    }
}
