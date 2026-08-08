<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;

final class MushCrewProportionRequirement extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::MUSH_CREW_PROPORTION;

    public function checkRequirement(ModifierActivationRequirement $modifierRequirement, ModifierHolderInterface $holder): bool
    {
        $daedalus = $holder->getDaedalus();
        if (!$daedalus->getDaedalusInfo()->isDaedalusStarted()) {
            return false;
        }

        $players = $daedalus->getPlayers();
        $crewCount = $daedalus->getDaedalusConfig()->getPlayerCount();
        $cryogenizedPlayersCount = $crewCount - $players->count();
        $mushOrDeadPlayersCount = $players
            ->filter(static fn (Player $player): bool => $player->isMush() || $player->isDead())
            ->count();
        $panicProportion = ($mushOrDeadPlayersCount + $cryogenizedPlayersCount) / $crewCount;

        return $panicProportion >= $modifierRequirement->getValue() / 100;
    }
}
