<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Class DifficultyConfig.
 *
 * @ORM\Entity()
 * @ORM\Table(name="config_difficulty")
 */
class DifficultyConfig
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Game\Entity\GameConfig", inversedBy="difficultyConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $equipmentBreakRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $doorBreakRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $equipmentFireBreakRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $startingFireRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $propagatingFireRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $tremorRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $electricArcRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $metalPlateRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $panicCrisisRate;

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
    public function setGameConfig(GameConfig $gameConfig): DifficultyConfig
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getEquipmentBreakRate(): int
    {
        return $this->equipmentBreakRate;
    }

    /**
     * @return static
     */
    public function setEquipmentBreakRate(int $equipmentBreakRate): DifficultyConfig
    {
        $this->equipmentBreakRate = $equipmentBreakRate;

        return $this;
    }

    public function getDoorBreakRate(): int
    {
        return $this->doorBreakRate;
    }

    /**
     * @return static
     */
    public function setDoorBreakRate(int $doorBreakRate): DifficultyConfig
    {
        $this->doorBreakRate = $doorBreakRate;

        return $this;
    }

    public function getEquipmentFireBreakRate(): int
    {
        return $this->equipmentFireBreakRate;
    }

    /**
     * @return static
     */
    public function setEquipmentFireBreakRate(int $equipmentFireBreakRate): DifficultyConfig
    {
        $this->equipmentFireBreakRate = $equipmentFireBreakRate;

        return $this;
    }

    public function getStartingFireRate(): int
    {
        return $this->startingFireRate;
    }

    /**
     * @return static
     */
    public function setStartingFireRate(int $startingFireRate): DifficultyConfig
    {
        $this->startingFireRate = $startingFireRate;

        return $this;
    }

    public function getPropagatingFireRate(): int
    {
        return $this->propagatingFireRate;
    }

    /**
     * @return static
     */
    public function setPropagatingFireRate(int $propagatingFireRate): DifficultyConfig
    {
        $this->propagatingFireRate = $propagatingFireRate;

        return $this;
    }

    public function getTremorRate(): int
    {
        return $this->tremorRate;
    }

    /**
     * @return static
     */
    public function setTremorRate(int $tremorRate): DifficultyConfig
    {
        $this->tremorRate = $tremorRate;

        return $this;
    }

    public function getElectricArcRate(): int
    {
        return $this->electricArcRate;
    }

    /**
     * @return static
     */
    public function setElectricArcRate(int $electricArcRate): DifficultyConfig
    {
        $this->electricArcRate = $electricArcRate;

        return $this;
    }

    public function getMetalPlateRate(): int
    {
        return $this->metalPlateRate;
    }

    /**
     * @return static
     */
    public function setMetalPlateRate(int $metalPlateRate): DifficultyConfig
    {
        $this->metalPlateRate = $metalPlateRate;

        return $this;
    }

    public function getPanicCrisisRate(): int
    {
        return $this->panicCrisisRate;
    }

    /**
     * @return static
     */
    public function setPanicCrisisRate(int $panicCrisisRate): DifficultyConfig
    {
        $this->panicCrisisRate = $panicCrisisRate;

        return $this;
    }
}
