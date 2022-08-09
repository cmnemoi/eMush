<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\IsMedlabRoom;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * The class MedlabHeal implements the `Heal`
 * action with the Medlab, which should be available
 * if the player doesn't have the medikit on their
 * inventory and is in the medlab (to avoid duplicate action).
 *
 * For more details about how the `Heal` action work,
 * see the `AbstractHeal` class.
 */
class MedlabHeal extends AbstractHeal
{
    protected string $name = ActionEnum::MEDLAB_HEAL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new IsMedlabRoom([
            'groups' => ['visibility'],
            'expectedValue' => true,
        ]));
    }
}
