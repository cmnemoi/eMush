<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;

final readonly class ModifyMaxDamageWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct() {}

    public function getName(): string
    {
        return WeaponEffectEnum::MODIFY_MAX_DAMAGE->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $effect->modifyMaxDamage();
    }

    public function isModifyingDamages(): bool
    {
        return true;
    }
}
