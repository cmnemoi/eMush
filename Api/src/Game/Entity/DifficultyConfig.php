<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\Table(name: 'config_difficulty')]
class DifficultyConfig implements ConfigInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: GameConfig::class, inversedBy: 'difficultyConfig')]
    private GameConfig $gameConfig;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $equipmentBreakRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $doorBreakRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $equipmentFireBreakRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $startingFireRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $propagatingFireRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $hullFireDamageRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $tremorRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $electricArcRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $metalPlateRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $panicCrisisRate = 0;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $firePlayerDamage = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $fireHullDamage = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $electricArcPlayerDamage = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $tremorPlayerDamage = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $metalPlatePlayerDamage = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $panicCrisisPlayerDamage = [];

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $plantDiseaseRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycleDiseaseRate = 0;

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

    public function getEquipmentBreakRate(): int
    {
        return $this->equipmentBreakRate;
    }

    public function setEquipmentBreakRate(int $equipmentBreakRate): static
    {
        $this->equipmentBreakRate = $equipmentBreakRate;

        return $this;
    }

    public function getDoorBreakRate(): int
    {
        return $this->doorBreakRate;
    }

    public function setDoorBreakRate(int $doorBreakRate): static
    {
        $this->doorBreakRate = $doorBreakRate;

        return $this;
    }

    public function getEquipmentFireBreakRate(): int
    {
        return $this->equipmentFireBreakRate;
    }

    public function setEquipmentFireBreakRate(int $equipmentFireBreakRate): static
    {
        $this->equipmentFireBreakRate = $equipmentFireBreakRate;

        return $this;
    }

    public function getStartingFireRate(): int
    {
        return $this->startingFireRate;
    }

    public function setStartingFireRate(int $startingFireRate): static
    {
        $this->startingFireRate = $startingFireRate;

        return $this;
    }

    public function getPropagatingFireRate(): int
    {
        return $this->propagatingFireRate;
    }

    public function setPropagatingFireRate(int $propagatingFireRate): static
    {
        $this->propagatingFireRate = $propagatingFireRate;

        return $this;
    }

    public function getHullFireDamageRate(): int
    {
        return $this->hullFireDamageRate;
    }

    public function setHullFireDamageRate(int $hullFireDamageRate): static
    {
        $this->hullFireDamageRate = $hullFireDamageRate;

        return $this;
    }

    public function getTremorRate(): int
    {
        return $this->tremorRate;
    }

    public function setTremorRate(int $tremorRate): static
    {
        $this->tremorRate = $tremorRate;

        return $this;
    }

    public function getElectricArcRate(): int
    {
        return $this->electricArcRate;
    }

    public function setElectricArcRate(int $electricArcRate): static
    {
        $this->electricArcRate = $electricArcRate;

        return $this;
    }

    public function getMetalPlateRate(): int
    {
        return $this->metalPlateRate;
    }

    public function setMetalPlateRate(int $metalPlateRate): static
    {
        $this->metalPlateRate = $metalPlateRate;

        return $this;
    }

    public function getPanicCrisisRate(): int
    {
        return $this->panicCrisisRate;
    }

    public function setPanicCrisisRate(int $panicCrisisRate): static
    {
        $this->panicCrisisRate = $panicCrisisRate;

        return $this;
    }

    public function getFirePlayerDamage(): array
    {
        return $this->firePlayerDamage;
    }

    public function setFirePlayerDamage(array $firePlayerDamage): static
    {
        $this->firePlayerDamage = $firePlayerDamage;

        return $this;
    }

    public function getFireHullDamage(): array
    {
        return $this->fireHullDamage;
    }

    public function setFireHullDamage(array $fireHullDamage): static
    {
        $this->fireHullDamage = $fireHullDamage;

        return $this;
    }

    public function getElectricArcPlayerDamage(): array
    {
        return $this->electricArcPlayerDamage;
    }

    public function setElectricArcPlayerDamage(array $electricArcPlayerDamage): static
    {
        $this->electricArcPlayerDamage = $electricArcPlayerDamage;

        return $this;
    }

    public function getTremorPlayerDamage(): array
    {
        return $this->tremorPlayerDamage;
    }

    public function setTremorPlayerDamage(array $tremorPlayerDamage): static
    {
        $this->tremorPlayerDamage = $tremorPlayerDamage;

        return $this;
    }

    public function getMetalPlatePlayerDamage(): array
    {
        return $this->metalPlatePlayerDamage;
    }

    public function setMetalPlatePlayerDamage(array $metalPlatePlayerDamage): static
    {
        $this->metalPlatePlayerDamage = $metalPlatePlayerDamage;

        return $this;
    }

    public function getPanicCrisisPlayerDamage(): array
    {
        return $this->panicCrisisPlayerDamage;
    }

    public function setPanicCrisisPlayerDamage(array $panicCrisisPlayerDamage): static
    {
        $this->panicCrisisPlayerDamage = $panicCrisisPlayerDamage;

        return $this;
    }

    public function getPlantDiseaseRate(): int
    {
        return $this->plantDiseaseRate;
    }

    public function setPlantDiseaseRate(int $plantDiseaseRate): static
    {
        $this->plantDiseaseRate = $plantDiseaseRate;

        return $this;
    }

    public function getCycleDiseaseRate(): int
    {
        return $this->cycleDiseaseRate;
    }

    public function setCycleDiseaseRate(int $cycleDiseaseRate): static
    {
        $this->cycleDiseaseRate = $cycleDiseaseRate;

        return $this;
    }
}
