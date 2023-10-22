<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\Exploration;
use Mush\Player\Entity\Collection\PlayerCollection;

interface ExplorationServiceInterface
{
    public function createExploration(PlayerCollection $players, array $reasons): Exploration;

    public function closeExploration(Exploration $exploration, array $reasons): void;

    public function computeExplorationEvents(Exploration $exploration): Exploration;
}
