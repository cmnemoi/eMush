<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Player\Entity\Player;

interface PlanetServiceInterface
{
    public function createPlanet(Player $player): Planet;

    public function findById(int $id): ?Planet;

    public function findPlanetSectorById(int $id): ?PlanetSector;

    public function persist(array $entities): void;
}
