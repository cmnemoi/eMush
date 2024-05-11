<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\PlaceType;
use Mush\Place\Enum\PlaceTypeEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class ShootHunterPatrolShip extends ShootHunter
{
    protected ActionEnum $name = ActionEnum::SHOOT_HUNTER_PATROL_SHIP;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        parent::loadValidatorMetadata($metadata);
        $metadata->addConstraint(new PlaceType(['type' => PlaceTypeEnum::PATROL_SHIP, 'groups' => ['visibility']]));
    }
}
