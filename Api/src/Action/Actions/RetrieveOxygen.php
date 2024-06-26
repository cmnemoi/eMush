<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class RetrieveOxygen extends RetrieveAction
{
    protected ActionEnum $name = ActionEnum::RETRIEVE_OXYGEN;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'variableName' => DaedalusVariableEnum::OXYGEN,
            'target' => GameVariableLevel::DAEDALUS,
            'checkMode' => GameVariableLevel::IS_MIN,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function getDaedalusVariable(): string
    {
        return DaedalusVariableEnum::OXYGEN;
    }

    protected function getItemName(): string
    {
        return ItemEnum::OXYGEN_CAPSULE;
    }
}
