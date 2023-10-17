<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\Planet;
use Mush\Player\Entity\Player;

interface PlanetServiceInterface
{
    public function createPlanet(Player $player): Planet;
}
