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
class DifficultyConfig implements ConfigInterface
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
    private int $equipmentBreakRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $doorBreakRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $equipmentFireBreakRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $startingFireRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $propagatingFireRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $hullFireDamageRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $tremorRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $electricArcRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $metalPlateRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $panicCrisisRate = 0;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $firePlayerDamage = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $fireHullDamage = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $electricArcPlayerDamage = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $tremorPlayerDamage = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $metalPlatePlayerDamage = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $panicCrisisPlayerDamage = [];

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $plantDiseaseRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cycleDiseaseRate = 0;

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
    public function setGameConfig(GameConfig $gameConfig): self
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
    public function setEquipmentBreakRate(int $equipmentBreakRate): self
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
    public function setDoorBreakRate(int $doorBreakRate): self
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
    public function setEquipmentFireBreakRate(int $equipmentFireBreakRate): self
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
    public function setStartingFireRate(int $startingFireRate): self
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
    public function setPropagatingFireRate(int $propagatingFireRate): self
    {
        $this->propagatingFireRate = $propagatingFireRate;

        return $this;
    }

    public function getHullFireDamageRate(): int
    {
        return $this->hullFireDamageRate;
    }

    /**
     * @return static
     */
    public function setHullFireDamageRate(int $hullFireDamageRate): self
    {
        $this->hullFireDamageRate = $hullFireDamageRate;

        return $this;
    }

    public function getTremorRate(): int
    {
        return $this->tremorRate;
    }

    /**
     * @return static
     */
    public function setTremorRate(int $tremorRate): self
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
    public function setElectricArcRate(int $electricArcRate): self
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
    public function setMetalPlateRate(int $metalPlateRate): self
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
    public function setPanicCrisisRate(int $panicCrisisRate): self
    {
        $this->panicCrisisRate = $panicCrisisRate;

        return $this;
    }

    public function getFirePlayerDamage(): array
    {
        return $this->firePlayerDamage;
    }

    /**
     * @return static
     */
    public function setFirePlayerDamage(array $firePlayerDamage): self
    {
        $this->firePlayerDamage = $firePlayerDamage;

        return $this;
    }

    public function getFireHullDamage(): array
    {
        return $this->fireHullDamage;
    }

    /**
     * @return static
     */
    public function setFireHullDamage(array $fireHullDamage): self
    {
        $this->fireHullDamage = $fireHullDamage;

        return $this;
    }

    public function getElectricArcPlayerDamage(): array
    {
        return $this->electricArcPlayerDamage;
    }

    /**
     * @return static
     */
    public function setElectricArcPlayerDamage(array $electricArcPlayerDamage): self
    {
        $this->electricArcPlayerDamage = $electricArcPlayerDamage;

        return $this;
    }

    public function getTremorPlayerDamage(): array
    {
        return $this->tremorPlayerDamage;
    }

    /**
     * @return static
     */
    public function setTremorPlayerDamage(array $tremorPlayerDamage): self
    {
        $this->tremorPlayerDamage = $tremorPlayerDamage;

        return $this;
    }

    public function getMetalPlatePlayerDamage(): array
    {
        return $this->metalPlatePlayerDamage;
    }

    /**
     * @return static
     */
    public function setMetalPlatePlayerDamage(array $metalPlatePlayerDamage): self
    {
        $this->metalPlatePlayerDamage = $metalPlatePlayerDamage;

        return $this;
    }

    public function getPanicCrisisPlayerDamage(): array
    {
        return $this->panicCrisisPlayerDamage;
    }

    /**
     * @return static
     */
    public function setPanicCrisisPlayerDamage(array $panicCrisisPlayerDamage): self
    {
        $this->panicCrisisPlayerDamage = $panicCrisisPlayerDamage;

        return $this;
    }

    public function getPlantDiseaseRate(): int
    {
        return $this->plantDiseaseRate;
    }

    /**
     * @return static
     */
    public function setPlantDiseaseRate(int $plantDiseaseRate): self
    {
        $this->plantDiseaseRate = $plantDiseaseRate;

        return $this;
    }

    public function getCycleDiseaseRate(): int
    {
        return $this->cycleDiseaseRate;
    }

    /**
     * @return static
     */
    public function setCycleDiseaseRate(int $cycleDiseaseRate): self
    {
        $this->cycleDiseaseRate = $cycleDiseaseRate;

        return $this;
    }
}
