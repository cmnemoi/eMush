<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\ExplorationPlanetSectorEvent;
use Mush\Player\Entity\Collection\PlayerCollection;

interface ExplorationServiceInterface
{
    public function createExploration(PlayerCollection $players, array $reasons): Exploration;

    public function closeExploration(Exploration $exploration, array $reasons): ClosedExploration;

    public function computeExplorationEvents(Exploration $exploration): Exploration;

    public function createExplorationLog(ExplorationPlanetSectorEvent $event, array $parameters = []): ExplorationLog;

    public function persist(array $entities): void;
}
