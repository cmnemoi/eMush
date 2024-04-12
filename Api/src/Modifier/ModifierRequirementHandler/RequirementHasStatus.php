<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\StatusHolderInterface;

class RequirementHasStatus extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::HOLDER_HAS_STATUS;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if (!$holder instanceof StatusHolderInterface) {
            throw new \LogicException('STATUS activationRequirement can only be applied on a statusHolder');
        }
        /** @var Player $player */
        $player = $holder;
        $expectedStatus = $modifierRequirement->getActivationRequirement();
        if ($expectedStatus === null) {
            throw new \LogicException('provide a status for player_status activationRequirement');
        }

        if ($modifierRequirement->getValue() === ModifierRequirementEnum::ABSENT_STATUS) {
            return !$player->hasStatus($expectedStatus);
        }

        return $player->hasStatus($expectedStatus);
    }
}
