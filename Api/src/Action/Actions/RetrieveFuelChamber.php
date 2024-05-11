<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class RetrieveFuelChamber extends RetrieveAction
{
    protected ActionEnum $name = ActionEnum::RETRIEVE_FUEL_CHAMBER;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new GameVariableLevel([
                'target' => GameVariableLevel::DAEDALUS,
                'checkMode' => GameVariableLevel::IS_MIN,
                'variableName' => DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL,
                'groups' => ['visibility'],
            ]),
            new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]),
        ]);
    }

    protected function checkResult(): ActionResult
    {
        // Send the new amount of fuel in the combustion chamber for it to be used in the success log
        $result = new Success();
        $result->setQuantity($this->player->getDaedalus()->getCombustionChamberFuel() - 1);

        return $result;
    }

    protected function getDaedalusVariable(): string
    {
        return DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL;
    }

    protected function getItemName(): string
    {
        return ItemEnum::FUEL_CAPSULE;
    }
}
