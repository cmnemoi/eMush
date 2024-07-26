<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;

final class RequirementHolderHasNotSkill extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::HOLDER_HAS_NOT_SKILL;

    public function checkRequirement(ModifierActivationRequirement $modifierRequirement, ModifierHolderInterface $holder): bool
    {
        if (!$holder instanceof Player) {
            throw new \InvalidArgumentException("{$this->name} activation requirement can only be applied to a Player modifier holder, got a {$holder->getClassName()} {$holder->getName()} instead.");
        }

        $skillToCheck = $modifierRequirement->getActivationRequirement();
        if ($skillToCheck === null) {
            throw new \InvalidArgumentException("{$this->name} activation requirement value is missing.");
        }

        return $holder->hasSkill(SkillEnum::from($skillToCheck)) === false;
    }
}
