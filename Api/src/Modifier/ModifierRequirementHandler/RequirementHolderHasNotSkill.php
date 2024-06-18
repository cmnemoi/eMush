<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;

final class RequirementHolderHasNotSkill extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::HOLDER_HAS_NOT_SKILL;

    public function checkRequirement(ModifierActivationRequirement $modifierActivationRequirement, ModifierHolderInterface $holder): bool
    {
        if (!$holder instanceof Player) {
            throw new \InvalidArgumentException("{$this->name} activationRequirement can only be applied to a Player modifier holder, got a {$holder->getClassName()} {$holder->getName()} instead.");
        }

        return $holder->hasSkill($modifierActivationRequirement->getActivationRequirement()) === false;
    }
}
