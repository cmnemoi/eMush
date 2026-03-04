<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;

final readonly class ModifyMaxDamageFromSkilledAttackerWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct() {}

    public function getName(): string
    {
        return WeaponEffectEnum::MODIFY_MAX_DAMAGE_FROM_SKILLED_ATTACKER->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $attacker = $effect->getAttacker();

        if ($attacker->hasStatus(PlayerStatusEnum::BERZERK)) {
            return;
        }

        if ($attacker->hasSkill(SkillEnum::WRESTLER)) {
            $effect->modifyMaxDamageBy(2);

            return;
        }

        if ($attacker->hasSkill(SkillEnum::SOLID)) {
            $effect->modifyMaxDamageBy(1);

            return;
        }
    }

    public function isModifyingDamages(): bool
    {
        return true;
    }
}
