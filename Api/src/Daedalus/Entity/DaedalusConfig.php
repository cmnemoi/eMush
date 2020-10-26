<?php


namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;

/**
 * Class DaedalusConfig
 * @package Mush\Daedalus\Entity
 * @ORM\Entity()
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
     * @ORM\OneToOne (targetEntity="Mush\Game\Entity\GameConfig")
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
     * @ORM\OneToOne(targetEntity="Mush\Daedalus\Entity\RandomItemPlaces", cascade={"ALL"})
     */
    private ?RandomItemPlaces $randomItemPlace = null;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Room\Entity\RoomConfig", mappedBy="daedalusConfig")
     */
    private Collection $roomConfigs;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): DaedalusConfig
    {
        $this->gameConfig = $gameConfig;
        return $this;
    }

    public function getInitOxygen(): int
    {
        return $this->initOxygen;
    }

    public function setInitOxygen(int $initOxygen): DaedalusConfig
    {
        $this->initOxygen = $initOxygen;
        return $this;
    }

    public function getInitFuel(): int
    {
        return $this->initFuel;
    }

    public function setInitFuel(int $initFuel): DaedalusConfig
    {
        $this->initFuel = $initFuel;
        return $this;
    }

    public function getInitHull(): int
    {
        return $this->initHull;
    }

    public function setInitHull(int $initHull): DaedalusConfig
    {
        $this->initHull = $initHull;
        return $this;
    }

    public function getInitShield(): int
    {
        return $this->initShield;
    }

    public function setInitShield(int $initShield): DaedalusConfig
    {
        $this->initShield = $initShield;
        return $this;
    }

    public function getRandomItemPlace(): ?RandomItemPlaces
    {
        return $this->randomItemPlace;
    }

    public function setRandomItemPlace(RandomItemPlaces $randomItemPlace): DaedalusConfig
    {
        $this->randomItemPlace = $randomItemPlace;
        return $this;
    }

    public function getRoomConfigs(): Collection
    {
        return $this->roomConfigs;
    }

    public function setRoomConfigs(Collection $roomConfigs): DaedalusConfig
    {
        $this->roomConfigs = $roomConfigs;
        return $this;
    }
}
