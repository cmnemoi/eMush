<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Equipment\Enum\ItemEnum;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;

class RequirementAnyWeaponInInventory extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::PLAYER_ANY_WEAPON;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if (!$holder instanceof Player) {
            throw new \LogicException('PLAYER_ANY_WEAPON activationRequirement can only be applied on a player');
        }

        /** @var Player $player */
        $player = $holder;

        return $player->hasAnyOperationalEquipment(ItemEnum::getWeapons()->toArray());
    }
}
