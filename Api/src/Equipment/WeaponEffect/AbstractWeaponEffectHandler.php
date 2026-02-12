<?php

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Event\WeaponEffect;

abstract readonly class AbstractWeaponEffectHandler
{
    abstract public function getName(): string;

    abstract public function handle(WeaponEffect $effect): void;

    abstract public function isModifyingDamages(): bool;
}
