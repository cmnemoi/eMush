<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Status\Entity\StatusHolderInterface;

class RequirementHasStatus extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::HOLDER_HAS_STATUS;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if (!$holder instanceof StatusHolderInterface) {
            throw new \LogicException('STATUS activationRequirement can only be applied on a statusHolder');
        }

        $expectedStatus = $modifierRequirement->getActivationRequirementOrThrow();

        return ($modifierRequirement->getValue() === ModifierRequirementEnum::ABSENT_STATUS)
            ? !$holder->hasStatus($expectedStatus) : $holder->hasStatus($expectedStatus);
    }
}
