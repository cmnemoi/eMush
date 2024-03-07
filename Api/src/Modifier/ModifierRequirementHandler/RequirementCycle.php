<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;

class RequirementCycle extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::CYCLE;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        $daedalus = $holder->getDaedalus();

        switch ($modifierRequirement->getActivationRequirement()) {
            case ModifierRequirementEnum::EVEN:
                return $daedalus->getCycle() / 2 === intval($daedalus->getCycle() / 2);

            default:
                throw new \LogicException('This activationRequirement is invalid for cycle');
        }
    }
}
