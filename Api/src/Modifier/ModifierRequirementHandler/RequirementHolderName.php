<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;

class RequirementHolderName extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::HOLDER_NAME;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        return $modifierRequirement->getActivationRequirement() === $holder->getName();
    }
}
