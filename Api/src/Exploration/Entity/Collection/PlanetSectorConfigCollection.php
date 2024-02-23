<?php

namespace Mush\Exploration\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Exploration\Entity\PlanetSectorConfig;

/**
 * @template-extends ArrayCollection<int, PlanetSectorConfig>
 */
class PlanetSectorConfigCollection extends ArrayCollection
{
    public function getBySectorName(string $sectorName): PlanetSectorConfig
    {
        $planetSectorConfig = $this
            ->filter(fn (PlanetSectorConfig $planetSectorConfig) => $planetSectorConfig->getSectorName() === $sectorName)
            ->first()
        ;

        if (!$planetSectorConfig) {
            throw new \Exception("PlanetSectorConfig $sectorName not found");
        }

        return $planetSectorConfig;
    }
}
