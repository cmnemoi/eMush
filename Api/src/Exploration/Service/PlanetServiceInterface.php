<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\Entity\Planet;

interface PlanetServiceInterface
{
    public function createPlanet(): Planet;
}