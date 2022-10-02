<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\PlaceConfig;

#[ORM\Entity]
#[ORM\Table(name: 'config_daedalus')]
class DaedalusConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'daedalusConfig', targetEntity: GameConfig::class)]
    private GameConfig $gameConfig;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initOxygen;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initFuel;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initHull;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initShield;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxOxygen = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxFuel = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxHull = 0;

    #[ORM\OneToOne(targetEntity: RandomItemPlaces::class, cascade: ['ALL'])]
    private ?RandomItemPlaces $randomItemPlace = null;

    #[ORM\OneToMany(targetEntity: PlaceConfig::class, mappedBy: 'daedalusConfig')]
    private Collection $placeConfigs;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $dailySporeNb = 4;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): static
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getInitOxygen(): int
    {
        return $this->initOxygen;
    }

    public function setInitOxygen(int $initOxygen): static
    {
        $this->initOxygen = $initOxygen;

        return $this;
    }

    public function getInitFuel(): int
    {
        return $this->initFuel;
    }

    public function setInitFuel(int $initFuel): static
    {
        $this->initFuel = $initFuel;

        return $this;
    }

    public function getInitHull(): int
    {
        return $this->initHull;
    }

    public function setInitHull(int $initHull): static
    {
        $this->initHull = $initHull;

        return $this;
    }

    public function getInitShield(): int
    {
        return $this->initShield;
    }

    public function setInitShield(int $initShield): static
    {
        $this->initShield = $initShield;

        return $this;
    }

    public function getRandomItemPlace(): ?RandomItemPlaces
    {
        return $this->randomItemPlace;
    }

    public function setRandomItemPlace(RandomItemPlaces $randomItemPlace): static
    {
        $this->randomItemPlace = $randomItemPlace;

        return $this;
    }

    public function getPlaceConfigs(): Collection
    {
        return $this->placeConfigs;
    }

    public function setPlaceConfigs(Collection $placeConfigs): static
    {
        $this->placeConfigs = $placeConfigs;

        return $this;
    }

    public function getDailySporeNb(): int
    {
        return $this->dailySporeNb;
    }

    public function setDailySporeNb(int $dailySporeNb): static
    {
        $this->dailySporeNb = $dailySporeNb;

        return $this;
    }

    public function getMaxOxygen(): int
    {
        return $this->maxOxygen;
    }

    public function setMaxOxygen(int $maxOxygen): static
    {
        $this->maxOxygen = $maxOxygen;

        return $this;
    }

    public function getMaxFuel(): int
    {
        return $this->maxFuel;
    }

    public function setMaxFuel(int $maxFuel): static
    {
        $this->maxFuel = $maxFuel;

        return $this;
    }

    public function getMaxHull(): int
    {
        return $this->maxHull;
    }

    public function setMaxHull(int $maxHull): static
    {
        $this->maxHull = $maxHull;

        return $this;
    }
}
