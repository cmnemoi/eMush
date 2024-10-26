<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;

final class MushCrewProportionRequirement extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::MUSH_CREW_PROPORTION;

    public function checkRequirement(ModifierActivationRequirement $modifierRequirement, ModifierHolderInterface $holder): bool
    {
        $alivePlayers = $holder->getDaedalus()->getAlivePlayers();

        $alivePlayersCount = $alivePlayers->count();
        $mushPlayersCount = $alivePlayers->getMushPlayer()->count();
        $mushProportion = $mushPlayersCount / $alivePlayersCount;

        return $mushProportion > $modifierRequirement->getValue() / 100;
    }
}
