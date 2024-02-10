<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\ParameterName;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class InsertFuel extends InsertAction
{
    protected string $name = ActionEnum::INSERT_FUEL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new ParameterName([
                'names' => [ToolItemEnum::JAR_OF_ALIEN_OIL, ItemEnum::FUEL_CAPSULE],
                'groups' => ['visibility'],
            ]),
            new GameVariableLevel([
                'target' => GameVariableLevel::DAEDALUS,
                'checkMode' => GameVariableLevel::IS_MAX,
                'variableName' => DaedalusVariableEnum::FUEL,
                'groups' => ['visibility'],
            ]),
        ]);
    }

    protected function getDaedalusVariable(): string
    {
        return DaedalusVariableEnum::FUEL;
    }
}
