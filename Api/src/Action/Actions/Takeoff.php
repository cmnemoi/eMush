<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\PlaceType;
use Mush\Place\Enum\PlaceTypeEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Takeoff extends Move
{
    protected string $name = ActionEnum::TAKEOFF;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        parent::loadValidatorMetadata($metadata);
        $metadata->addConstraint(new PlaceType(['groups' => ['visibility'], 'type' => PlaceTypeEnum::ROOM]));
    }
}
