<?php

namespace Mush\Disease\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;

/**
 * @ORM\Entity
 * @ORM\Table(name="disease_config")
 */
class DiseaseConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * Duration is -1 for permanent effects
     */
    private int $duration = -1;

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Disease\Entity\DiseaseCause")
     */
    private Collection $causes;

    public function __construct()
    {
        $this->causes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): DiseaseConfig
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DiseaseConfig
    {
        $this->name = $name;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): DiseaseConfig
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCauses(): Collection
    {
        return $this->causes;
    }

    public function getCauseByName(string $causeName): ?DiseaseCause
    {
        $cause = $this->causes->filter(fn (DiseaseCause $cause) => $cause->getName() === $causeName)->first();

        return $cause ?: null;
    }

    public function setCauses(Collection $causes): DiseaseConfig
    {
        $this->causes = $causes;

        return $this;
    }
}
