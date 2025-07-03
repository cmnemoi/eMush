<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Planet;

interface CreateAPlanetInOrbitServiceInterface
{
    public function execute(Daedalus $daedalus, bool $revealAllSectors = false): Planet;
}
