<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Game\Entity\GameConfig;

/**
 * @ORM\Entity
 * @ORM\Table(name="disease_cause_config")
 */
class DiseaseCauseConfig
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
     * @ORM\Column (type="array")
     */
    private array $alienFruitDiseases = [];

    /**
     * @ORM\Column (type="array")
     */
    private array $cycleDiseases = [];

    /**
     * @ORM\Column (type="array")
     */
    private array $perishedFoodDiseases = [];

    /**
     * @ORM\Column (type="array")
     */
    private array $bacteriophilicDiseases = [];

    /**
     * @ORM\Column (type="array")
     */
    private array $fakeDiseases = [];

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): self
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getAlienFruitDiseases(): array
    {
        return $this->alienFruitDiseases;
    }

    public function setAlienFruitDiseases(array $alienFruitDiseases): self
    {
        $this->alienFruitDiseases = $alienFruitDiseases;

        return $this;
    }

    public function getCycleDiseases(): array
    {
        return $this->cycleDiseases;
    }

    public function setCycleDiseases(array $cycleDiseases): self
    {
        $this->cycleDiseases = $cycleDiseases;

        return $this;
    }

    public function getPerishedFoodDiseases(): array
    {
        return $this->perishedFoodDiseases;
    }

    public function setPerishedFoodDiseases(array $perishedFoodDiseases): self
    {
        $this->perishedFoodDiseases = $perishedFoodDiseases;

        return $this;
    }

    public function getBacteriophilicDiseases(): array
    {
        return $this->bacteriophilicDiseases;
    }

    public function setBacteriophilicDiseases(array $bacteriophilicDiseases): self
    {
        $this->bacteriophilicDiseases = $bacteriophilicDiseases;

        return $this;
    }

    public function getFakeDiseases(): array
    {
        return $this->fakeDiseases;
    }

    public function setFakeDiseases(array $fakeDiseases): self
    {
        $this->fakeDiseases = $fakeDiseases;

        return $this;
    }

    public function getDiseasesByCause(string $cause): array
    {
        switch ($cause) {
            case DiseaseCauseEnum::PERISHED_FOOD:
                return $this->perishedFoodDiseases;

            case DiseaseCauseEnum::ALIEN_FRUIT:
                return $this->alienFruitDiseases;

            case DiseaseCauseEnum::CYCLE:
                return $this->cycleDiseases;
        }

        return [];
    }
}
