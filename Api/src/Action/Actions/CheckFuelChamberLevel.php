<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class CheckFuelChamberLevel extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CHECK_FUEL_CHAMBER_LEVEL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
        ]);
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        // Send the current amount of fuel in the combustion chamber for it to be used in the success log
        $result = new Success();
        $result->setQuantity($this->player->getDaedalus()->getCombustionChamberFuel());

        return $result;
    }

    protected function applyEffect(ActionResult $result): void {}
}
