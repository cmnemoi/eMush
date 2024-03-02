<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class RequirementPlayerInRoom extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::PLAYER_IN_ROOM;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if ($holder instanceof Place) {
            $room = $holder;
        } elseif ($holder instanceof GameEquipment || $holder instanceof Player) {
            $room = $holder->getPlace();
        } else {
            throw new \LogicException('daedalus cannot be used as holder for a player_in_room activationRequirement');
        }

        $players = $room->getPlayers()->getPlayerAlive();
        switch ($modifierRequirement->getActivationRequirement()) {
            case ModifierRequirementEnum::NOT_ALONE:
                return $players->count() >= 2;
            case ModifierRequirementEnum::ALONE:
                return $players->count() === 1;
            case ModifierRequirementEnum::FOUR_PEOPLE:
                return $players->count() >= 4;
            case ModifierRequirementEnum::MUSH_IN_ROOM:
                return $players->filter(fn (Player $player) => $player->isMush())->count() >= 1;

            default:
                throw new \LogicException('This activationRequirement is invalid for player_in_room');
        }
    }
}
