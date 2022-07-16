<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\IsMedlabRoom;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * The class MedikitHeal implements the `Heal`
 * action with the medikit, which should be available
 * if the player has the medikit on their
 * inventory and is not in the medlab (to avoid duplicate action).
 *
 * For more details about how the `Heal` action work,
 * see the `AbstractHeal` class.
 */
class MedikitHeal extends AbstractHeal
{
    protected string $name = ActionEnum::MEDIKIT_HEAL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new IsMedlabRoom([
            'groups' => ['visibility'],
            'expectedValue' => false,
        ]));
        $metadata->addConstraint(new HasEquipment([
            'groups' => ['visibility'],
            'equipment' => ToolItemEnum::MEDIKIT,
            'reach' => ReachEnum::INVENTORY,
            'contains' => true,
        ]));
    }
}
