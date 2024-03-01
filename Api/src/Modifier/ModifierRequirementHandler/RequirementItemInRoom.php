<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

abstract class RequirementItemInRoom extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::ITEM_IN_ROOM;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if ($holder instanceof Place) {
            $room = $holder;
        } elseif ($holder instanceof Player) {
            $room = $holder->getPlace();
        } else {
            throw new \LogicException('invalid ModifierHolderInterface for item_in_room activationRequirement');
        }

        return $room->getEquipments()->filter(function (GameEquipment $equipment) use ($modifierRequirement) {
            return $equipment->getName() === $modifierRequirement->getActivationRequirement();
        })->count() > 0;
    }
}
