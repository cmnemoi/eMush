<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\ClosedExploration;
use Mush\Player\Entity\Player;

interface ClosedExplorationServiceInterface
{
    public function getMostRecentForPlayer(Player $player): ClosedExploration;
}
