<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Enum\PlanetConfigsEnum;
use Mush\Player\Entity\Player;

interface PlanetServiceInterface
{
    public function createPlanet(Player $player, string $planetConfigName = PlanetConfigsEnum::REGULAR, ?string $forcedSector = null, ?int $fixedSize = null): Planet;

    public function regenerateAPlanet(Planet $planet, string $planetConfigName = PlanetConfigsEnum::REGULAR, ?string $forcedSector = null): Planet;

    public function revealPlanetSectors(Planet $planet, int $number): Planet;

    public function findById(int $id): ?Planet;

    /** Returns a `Daedalus`' `Planet` if it matches `Daedalus` destination. Else, returns `null`. */
    public function findOneByDaedalusDestination(Daedalus $daedalus): ?Planet;

    public function findAllByDaedalus(Daedalus $daedalus): ArrayCollection;

    /** Returns a `Planet` if Daedalus is in orbit around it. Else, returns `null`. */
    public function findPlanetInDaedalusOrbit(Daedalus $daedalus): ?Planet;

    public function delete(array $entities): void;

    public function persist(array $entities): void;
}
