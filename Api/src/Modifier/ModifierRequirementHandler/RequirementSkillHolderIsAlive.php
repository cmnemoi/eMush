<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Skill\Enum\SkillEnum;

final class RequirementSkillHolderIsAlive extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::SKILL_HOLDER_IS_ALIVE;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        $daedalus = $holder->getDaedalus();

        $skillToFind = $modifierRequirement->getActivationRequirementOrThrow();

        return $daedalus->getAlivePlayers()->getPlayersWithSkill(SkillEnum::from($skillToFind))->count() > 0;
    }
}
