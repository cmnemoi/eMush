<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Player\Entity\Collection\PlayerCollection;

interface ExplorationServiceInterface
{
    public function createExploration(PlayerCollection $players, array $reasons): Exploration;

    public function closeExploration(Exploration $exploration, array $reasons): ClosedExploration;

    public function computeExplorationEvents(Exploration $exploration): Exploration;

    public function createExplorationLog(PlanetSectorEvent $event, array $parameters = []): ExplorationLog;

    /**
     * @return array Array of log parameters to be used for the exploration log
     */
    public function removeHealthToARandomExplorator(PlanetSectorEvent $event): array;

    /**
     * @return array Array of log parameters to be used for the exploration log
     */
    public function removeHealthToAllExplorators(PlanetSectorEvent $event): array;

    public function persist(array $entities): void;
}
