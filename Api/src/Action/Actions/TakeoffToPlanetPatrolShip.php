<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\PlaceType;
use Mush\Place\Enum\PlaceTypeEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class TakeoffToPlanetPatrolShip extends TakeoffToPlanet
{
    protected string $name = ActionEnum::TAKEOFF_TO_PLANET_PATROL_SHIP;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        parent::loadValidatorMetadata($metadata);
        $metadata->addConstraint(new PlaceType(['type' => PlaceTypeEnum::PATROL_SHIP, 'groups' => ['visibility']]));
    }
}
