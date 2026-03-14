<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;

class RequirementBodyguardInRoom extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::BODYGUARD_IN_ROOM;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if (!$holder instanceof Player) {
            throw new \LogicException('BODYGUARD_IN_ROOM requirement need a player holder');
        }

        $bodyguard = $holder->getStatusByNameOrThrow(PlayerStatusEnum::BODYGUARD_VIP)->getPlayerTargetOrThrow();

        return $bodyguard->getPlace() === $holder->getPlace();
    }
}
