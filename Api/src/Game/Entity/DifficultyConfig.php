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
    private int $equipmentFireBreakRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $startingFireRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $propagatingFireRate;

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
}
