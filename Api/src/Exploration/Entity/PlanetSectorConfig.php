<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
#[ORM\Table(name: 'planet_sector_config')]
final class PlanetSectorConfig
{   
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $weightAtPlanetGeneration = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $weightAtPlanetAnalysis = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $weightAtPlanetExploration = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxPerPlanet = 0;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:1:{s:0:"";i:0;}'])]
    private array $explorationEvents = ['' => 0];

    public function getId(): int
    {
        return $this->id;
    }

    public function getWeightAtPlanetGeneration(): int
    {
        return $this->weightAtPlanetGeneration;
    }

    public function setWeightAtPlanetGeneration(int $weightAtPlanetGeneration): self
    {
        $this->weightAtPlanetGeneration = $weightAtPlanetGeneration;

        return $this;
    }

    public function getWeightAtPlanetAnalysis(): int
    {
        return $this->weightAtPlanetAnalysis;
    }

    public function setWeightAtPlanetAnalysis(int $weightAtPlanetAnalysis): self
    {
        $this->weightAtPlanetAnalysis = $weightAtPlanetAnalysis;

        return $this;
    }

    public function getWeightAtPlanetExploration(): int
    {
        return $this->weightAtPlanetExploration;
    }

    public function setWeightAtPlanetExploration(int $weightAtPlanetExploration): self
    {
        $this->weightAtPlanetExploration = $weightAtPlanetExploration;

        return $this;
    }

    public function getMaxPerPlanet(): int
    {
        return $this->maxPerPlanet;
    }

    public function setMaxPerPlanet(int $maxPerPlanet): self
    {
        $this->maxPerPlanet = $maxPerPlanet;

        return $this;
    }

    public function getExplorationEvents(): ProbaCollection
    {
        return new ProbaCollection($this->explorationEvents);
    }

    public function setExplorationEvents(ProbaCollection|array $explorationEvents): self
    {   
        if ($explorationEvents instanceof ProbaCollection) {
            $explorationEvents = $explorationEvents->toArray();
        }

        return $this;
    }

}