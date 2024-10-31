<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\DropWeaponEffectConfig;

final readonly class DropWeaponEffectConfigDto
{
    public function __construct(
        public string $name,
        public string $eventName,
    ) {}

    public function toEntity(): DropWeaponEffectConfig
    {
        return new DropWeaponEffectConfig($this->name, $this->eventName);
    }
}
