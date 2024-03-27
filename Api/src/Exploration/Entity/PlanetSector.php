<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
#[ORM\Table(name: 'planet_sector')]
class PlanetSector
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isRevealed = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isVisited = false;

    #[ORM\ManyToOne(targetEntity: PlanetSectorConfig::class)]
    private PlanetSectorConfig $planetSectorConfig;

    #[ORM\ManyToOne(targetEntity: Planet::class, inversedBy: 'sectors')]
    private Planet $planet;

    public function __construct(PlanetSectorConfig $planetSectorConfig, Planet $planet)
    {
        $this->planetSectorConfig = $planetSectorConfig;
        $this->planet = $planet;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isRevealed(): bool
    {
        return $this->isRevealed;
    }

    public function reveal(): self
    {
        $this->isRevealed = true;

        return $this;
    }

    public function isVisited(): bool
    {
        return $this->isVisited;
    }

    public function visit(): self
    {
        $this->isRevealed = true;
        $this->isVisited = true;

        return $this;
    }

    public function unvisit(): self
    {
        $this->isVisited = false;

        return $this;
    }

    public function getPlanet(): Planet
    {
        return $this->planet;
    }

    public function getName(): string
    {
        return $this->planetSectorConfig->getSectorName();
    }

    public function getWeightAtPlanetAnalysis(): int
    {
        return $this->planetSectorConfig->getWeightAtPlanetAnalysis();
    }

    public function getWeightAtPlanetExploration(): int
    {
        return $this->planetSectorConfig->getWeightAtPlanetExploration();
    }

    public function getExplorationEvents(): ProbaCollection
    {
        return $this->planetSectorConfig->getExplorationEvents();
    }
}
