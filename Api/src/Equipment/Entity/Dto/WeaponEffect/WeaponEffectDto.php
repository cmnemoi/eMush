<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto\WeaponEffect;

abstract readonly class WeaponEffectDto
{
    public function __construct(
        public string $name,
        public string $eventName,
    ) {}

    abstract public function toEntity();
}
