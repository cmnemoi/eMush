<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

interface ExplorationServiceInterface
{
    public function createExploration(PlayerCollection $players, GameEquipment $explorationShip, int $numberOfSectorsToVisit, array $reasons): Exploration;

    public function closeExploration(Exploration $exploration, array $reasons, ?Player $author = null): void;

    public function dispatchLandingEvent(Exploration $exploration): Exploration;

    public function dispatchExplorationEvent(Exploration $exploration): Exploration;

    public function getDummyExplorationForLostPlayer(ClosedExploration $closedExploration): Exploration;

    public function persist(array $entities): void;
}
