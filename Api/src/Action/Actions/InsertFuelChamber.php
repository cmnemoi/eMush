<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class InsertFuelChamber extends InsertAction
{
    protected string $name = ActionEnum::INSERT_FUEL_CHAMBER;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new HasEquipment([
                'reach' => ReachEnum::ROOM,
                'equipments' => [EquipmentEnum::COMBUSTION_CHAMBER],
                'checkIfOperational' => true,
                'groups' => ['visibility'],
            ]),
            new GameVariableLevel([
                'target' => GameVariableLevel::DAEDALUS,
                'checkMode' => GameVariableLevel::IS_MAX,
                'variableName' => DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::COMBUSTION_CHAMBER_FULL,
            ]),
        ]);
    }

    protected function checkResult(): ActionResult
    {
        // Send the new amount of fuel in the combustion chamber for it to be used in the success log
        $result = new Success();
        $result->setQuantity($this->player->getDaedalus()->getCombustionChamberFuel() + $this->getOutputQuantity());

        return $result;
    }

    protected function getDaedalusVariable(): string
    {
        return DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL;
    }
}
