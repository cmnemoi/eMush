<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Game\Entity\Collection\CharacterConfigCollection;
use Mush\Game\Entity\Collection\TriumphConfigCollection;

/**
 * Class Daedalus.
 *
 * @ORM\Entity(repositoryClass="Mush\Game\Repository\GameConfigRepository")
 * @ORM\Table(name="config_game")
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
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig", inversedBy="difficultyConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private string $equipmentBreakRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private string $equipmentFireBreakRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private string $startingFireRate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private string $propagatingFireRate;



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
