<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;

final readonly class MultiplyDamageOnMushTargetWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function getName(): string
    {
        return WeaponEffectEnum::MULTIPLY_DAMAGE_ON_MUSH_TARGET->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        if ($effect->getTarget()->isMush()) {
            $effect->multiplyDamage();
        }
    }

    public function isModifyingDamages(): bool
    {
        return true;
    }
}
