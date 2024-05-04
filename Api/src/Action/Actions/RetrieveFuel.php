<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class RetrieveFuel extends RetrieveAction
{
    protected ActionEnum $name = ActionEnum::RETRIEVE_FUEL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new GameVariableLevel([
                'target' => GameVariableLevel::DAEDALUS,
                'checkMode' => GameVariableLevel::IS_MIN,
                'variableName' => DaedalusVariableEnum::FUEL,
                'groups' => ['visibility'],
            ]),
            new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]),
            new HasStatus([
                'status' => EquipmentStatusEnum::BROKEN,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            ]),
        ]);
    }

    protected function getDaedalusVariable(): string
    {
        return DaedalusVariableEnum::FUEL;
    }

    protected function getItemName(): string
    {
        return ItemEnum::FUEL_CAPSULE;
    }
}
