<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\ParameterName;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class InsertOxygen extends InsertAction
{
    protected string $name = ActionEnum::INSERT_OXYGEN;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new ParameterName(['names' => [ItemEnum::OXYGEN_CAPSULE], 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'variableName' => DaedalusVariableEnum::OXYGEN,
            'target' => GameVariableLevel::DAEDALUS,
            'checkMode' => GameVariableLevel::IS_MAX,
            'groups' => ['visibility'],
        ]));
    }

    protected function getDaedalusVariable(): string
    {
        return DaedalusVariableEnum::OXYGEN;
    }
}
