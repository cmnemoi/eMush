<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\Planet;
use Mush\Player\Entity\Player;

interface PlanetServiceInterface
{
    public function createPlanet(Player $player): Planet;

    public function revealPlanetSectors(Planet $planet, int $number): Planet;

    public function findById(int $id): ?Planet;

    public function delete(array $entities): void;
}
