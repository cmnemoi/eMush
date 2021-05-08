<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;

/**
 * Class DaedalusConfig.
 *
 * @ORM\Entity()
 * @ORM\Table(name="config_daedalus")
 */
class DaedalusConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToOne (targetEntity="Mush\Game\Entity\GameConfig", inversedBy="daedalusConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initOxygen;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initFuel;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initHull;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initShield;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxOxygen = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxFuel = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxHull = 0;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Daedalus\Entity\RandomItemPlaces", cascade={"ALL"})
     */
    private ?RandomItemPlaces $randomItemPlace = null;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Place\Entity\PlaceConfig", mappedBy="daedalusConfig")
     */
    private Collection $placeConfigs;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $dailySporeNb = 4;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    /**
     * @return static
     */
    public function setGameConfig(GameConfig $gameConfig): DaedalusConfig
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getInitOxygen(): int
    {
        return $this->initOxygen;
    }

    /**
     * @return static
     */
    public function setInitOxygen(int $initOxygen): DaedalusConfig
    {
        $this->initOxygen = $initOxygen;

        return $this;
    }

    public function getInitFuel(): int
    {
        return $this->initFuel;
    }

    /**
     * @return static
     */
    public function setInitFuel(int $initFuel): DaedalusConfig
    {
        $this->initFuel = $initFuel;

        return $this;
    }

    public function getInitHull(): int
    {
        return $this->initHull;
    }

    /**
     * @return static
     */
    public function setInitHull(int $initHull): DaedalusConfig
    {
        $this->initHull = $initHull;

        return $this;
    }

    public function getInitShield(): int
    {
        return $this->initShield;
    }

    /**
     * @return static
     */
    public function setInitShield(int $initShield): DaedalusConfig
    {
        $this->initShield = $initShield;

        return $this;
    }

    public function getRandomItemPlace(): ?RandomItemPlaces
    {
        return $this->randomItemPlace;
    }

    /**
     * @return static
     */
    public function setRandomItemPlace(RandomItemPlaces $randomItemPlace): DaedalusConfig
    {
        $this->randomItemPlace = $randomItemPlace;

        return $this;
    }

    public function getPlaceConfigs(): Collection
    {
        return $this->placeConfigs;
    }

    /**
     * @return static
     */
    public function setPlaceConfigs(Collection $placeConfigs): DaedalusConfig
    {
        $this->placeConfigs = $placeConfigs;

        return $this;
    }

    public function getDailySporeNb(): int
    {
        return $this->dailySporeNb;
    }

    /**
     * @return static
     */
    public function setDailySporeNb(int $dailySporeNb): DaedalusConfig
    {
        $this->dailySporeNb = $dailySporeNb;

        return $this;
    }

    public function getMaxOxygen(): int
    {
        return $this->maxOxygen;
    }

    /**
     * @return static
     */
    public function setMaxOxygen(int $maxOxygen): DaedalusConfig
    {
        $this->maxOxygen = $maxOxygen;

        return $this;
    }

    public function getMaxFuel(): int
    {
        return $this->maxFuel;
    }

    /**
     * @return static
     */
    public function setMaxFuel(int $maxFuel): DaedalusConfig
    {
        $this->maxFuel = $maxFuel;

        return $this;
    }

    public function getMaxHull(): int
    {
        return $this->maxHull;
    }

    /**
     * @return static
     */
    public function setMaxHull(int $maxHull): DaedalusConfig
    {
        $this->maxHull = $maxHull;

        return $this;
    }
}
