<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class RequirementCycle extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::CYCLE;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if ($holder instanceof Place || $holder instanceof Player) {
            $daedalus = $holder->getDaedalus();
        } elseif ($holder instanceof GameEquipment) {
            $daedalus = $holder->getDaedalus();
        } elseif ($holder instanceof Daedalus) {
            $daedalus = $holder;
        } else {
            throw new \LogicException('This modifierHolder type is not handled');
        }

        switch ($modifierRequirement->getActivationRequirement()) {
            case ModifierRequirementEnum::EVEN:
                return $daedalus->getCycle() / 2 === intval($daedalus->getCycle() / 2);

            default:
                throw new \LogicException('This activationRequirement is invalid for cycle');
        }
    }
}
