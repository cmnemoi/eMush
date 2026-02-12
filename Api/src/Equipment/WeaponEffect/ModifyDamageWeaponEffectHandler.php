<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;

final readonly class ModifyDamageWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function getName(): string
    {
        return WeaponEffectEnum::MODIFY_DAMAGE->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $effect->modifyDamage();
    }

    public function isModifyingDamages(): bool
    {
        return true;
    }
}
