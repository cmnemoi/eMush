<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Repository\ClosedExplorationRepository;
use Mush\Player\Entity\Player;

final class ClosedExplorationService implements ClosedExplorationServiceInterface
{
    private ClosedExplorationRepository $closedExplorationRepository;

    public function __construct(ClosedExplorationRepository $closedExplorationRepository)
    {
        $this->closedExplorationRepository = $closedExplorationRepository;
    }

    public function getMostRecentForPlayer(Player $player): ClosedExploration
    {
        $closedExploration = $this->closedExplorationRepository->getMostRecentForPlayer($player);
        if (!$closedExploration) {
            throw new \RuntimeException('This player should have at least one exploration.');
        }

        return $closedExploration;
    }
}
