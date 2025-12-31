<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\PlaceType;
use Mush\Place\Enum\PlaceTypeEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class LieDownInShip extends LieDown
{
    protected ActionEnum $name = ActionEnum::LIE_DOWN_IN_SHIP;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new PlaceType(['type' => PlaceTypeEnum::PATROL_SHIP, 'groups' => ['visibility']]));
    }
}
