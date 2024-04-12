<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\Common\Collections\Criteria;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Player\Entity\Player;

final class ClosedExplorationService implements ClosedExplorationServiceInterface
{
    public function getMostRecentForPlayer(Player $player): ClosedExploration
    {
        $daedalusExplorations = $player->getDaedalus()->getDaedalusInfo()->getClosedExplorations();
        $playerExplorations = $daedalusExplorations->filter(static function (ClosedExploration $closedExploration) use ($player) {
            return $closedExploration->getClosedExplorators()->contains($player->getPlayerInfo()->getClosedPlayer());
        });

        if ($playerExplorations->isEmpty()) {
            throw new \RuntimeException('This player should have participated in at least one exploration');
        }

        return $playerExplorations->matching(Criteria::create()->orderBy(['createdAt' => Criteria::DESC]))->first();
    }
}
