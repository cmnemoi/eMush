<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\IsMedlabRoom;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * The class MedikitSelfHeal implements the `SelfHeal`
 * action with the medikit, which should be available
 * if the player has the medikit on their
 * inventory and is not in the medlab (to avoid duplicate action).
 *
 * For more details about how the `SelfHeal` action work,
 * see the `AbstractSelfHeal` class.
 */
class MedikitSelfHeal extends AbstractSelfHeal
{
    protected string $name = ActionEnum::MEDIKIT_SELF_HEAL;

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
