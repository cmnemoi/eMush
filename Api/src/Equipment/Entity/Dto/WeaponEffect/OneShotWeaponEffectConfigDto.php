<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

use Mush\Equipment\Entity\Config\WeaponEffect\OneShotWeaponEffectConfig;

final readonly class OneShotWeaponEffectConfigDto extends WeaponEffectDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public string $endCause,
    ) {}

    public function toEntity(): OneShotWeaponEffectConfig
    {
        return new OneShotWeaponEffectConfig($this->name, $this->eventName, $this->endCause);
    }
}
