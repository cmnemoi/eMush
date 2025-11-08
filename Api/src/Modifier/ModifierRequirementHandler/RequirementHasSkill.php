<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;

class RequirementHasSkill extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::HOLDER_HAS_SKILL;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if (!$holder instanceof Player) {
            throw new \LogicException('skill activationRequirement can only be applied on a Player');
        }
        $player = $holder;

        $activationRequirement = $modifierRequirement->getActivationRequirementOrThrow();
        $expectedSkill = SkillEnum::from($activationRequirement);

        return ($modifierRequirement->getValue() === ModifierRequirementEnum::ABSENT_SKILL)
            ? !$player->hasSkill($expectedSkill) : $player->hasSkill($expectedSkill);
    }
}
