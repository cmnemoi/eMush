<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;

abstract class RequirementItemInInventory extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::PLAYER_EQUIPMENT;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if (!$holder instanceof Player) {
            throw new \LogicException('PLAYER_EQUIPMENT activationRequirement can only be applied on a player');
        }

        /** @var Player $player */
        $player = $holder;

        $expectedItem = $modifierRequirement->getActivationRequirement();

        if ($expectedItem === null) {
            throw new \LogicException('provide an item for player_equipment activationRequirement');
        }

        return $player->hasEquipmentByName($expectedItem);
    }
}
