<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

final class RequirementSkillInRoom extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::SKILL_IN_ROOM;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if ($holder instanceof Place) {
            $room = $holder;
        } elseif ($holder instanceof GameEquipment || $holder instanceof Player) {
            $room = $holder->getPlace();
        } else {
            throw new \LogicException('daedalus cannot be used as holder for a skill_in_room activationRequirement');
        }

        $skillToFind = $modifierRequirement->getActivationRequirement();
        $alivePlayers = $room->getPlayers()->getPlayerAlive();

        return $alivePlayers->filter(static fn (Player $player) => $player->hasSkill($skillToFind))->count() > 0;
    }
}