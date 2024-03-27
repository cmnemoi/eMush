<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
#[ORM\Table(name: 'config_difficulty')]
class DifficultyConfig
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $name;

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
    private int $maximumAllowedSpreadingFires = 0;

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
    private array $firePlayerDamage;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $fireHullDamage;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $electricArcPlayerDamage;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $tremorPlayerDamage;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $metalPlatePlayerDamage;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $panicCrisisPlayerDamage;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $plantDiseaseRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycleDiseaseRate = 0;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $equipmentBreakRateDistribution;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $difficultyModes = [];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $hunterSpawnRate = 0;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $hunterSafeCycles = [];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $startingHuntersNumberOfTruceCycles = 0;

    public function __construct()
    {
        $this->firePlayerDamage = [];
        $this->fireHullDamage = [];
        $this->electricArcPlayerDamage = [];
        $this->tremorPlayerDamage = [];
        $this->metalPlatePlayerDamage = [];
        $this->panicCrisisPlayerDamage = [];
        $this->equipmentBreakRateDistribution = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getMaximumAllowedSpreadingFires(): int
    {
        return $this->maximumAllowedSpreadingFires;
    }

    public function setMaximumAllowedSpreadingFires(int $maximumAllowedSpreadingFires): static
    {
        $this->maximumAllowedSpreadingFires = $maximumAllowedSpreadingFires;

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

    public function getFirePlayerDamage(): ProbaCollection
    {
        return new ProbaCollection($this->firePlayerDamage);
    }

    public function setFirePlayerDamage(array $firePlayerDamage): static
    {
        $this->firePlayerDamage = $firePlayerDamage;

        return $this;
    }

    public function getFireHullDamage(): ProbaCollection
    {
        return new ProbaCollection($this->fireHullDamage);
    }

    public function setFireHullDamage(array $fireHullDamage): static
    {
        $this->fireHullDamage = $fireHullDamage;

        return $this;
    }

    public function getElectricArcPlayerDamage(): ProbaCollection
    {
        return new ProbaCollection($this->electricArcPlayerDamage);
    }

    public function setElectricArcPlayerDamage(array $electricArcPlayerDamage): static
    {
        $this->electricArcPlayerDamage = $electricArcPlayerDamage;

        return $this;
    }

    public function getTremorPlayerDamage(): ProbaCollection
    {
        return new ProbaCollection($this->tremorPlayerDamage);
    }

    public function setTremorPlayerDamage(array $tremorPlayerDamage): static
    {
        $this->tremorPlayerDamage = $tremorPlayerDamage;

        return $this;
    }

    public function getMetalPlatePlayerDamage(): ProbaCollection
    {
        return new ProbaCollection($this->metalPlatePlayerDamage);
    }

    public function setMetalPlatePlayerDamage(array $metalPlatePlayerDamage): static
    {
        $this->metalPlatePlayerDamage = $metalPlatePlayerDamage;

        return $this;
    }

    public function getPanicCrisisPlayerDamage(): ProbaCollection
    {
        return new ProbaCollection($this->panicCrisisPlayerDamage);
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

    public function getEquipmentBreakRateDistribution(): ProbaCollection
    {
        return new ProbaCollection($this->equipmentBreakRateDistribution);
    }

    public function setEquipmentBreakRateDistribution(array $equipmentBreakRateDistribution): static
    {
        $this->equipmentBreakRateDistribution = $equipmentBreakRateDistribution;

        return $this;
    }

    public function getDifficultyModes(): ArrayCollection
    {
        return new ArrayCollection($this->difficultyModes);
    }

    public function setDifficultyModes(array $difficultyModes): static
    {
        $this->difficultyModes = $difficultyModes;

        return $this;
    }

    public function getHunterSpawnRate(): int
    {
        return $this->hunterSpawnRate;
    }

    public function setHunterSpawnRate(int $hunterSpawnRate): static
    {
        $this->hunterSpawnRate = $hunterSpawnRate;

        return $this;
    }

    public function getHunterSafeCycles(): array
    {
        return $this->hunterSafeCycles;
    }

    public function setHunterSafeCycles(array $hunterSafeCycles): static
    {
        $this->hunterSafeCycles = $hunterSafeCycles;

        return $this;
    }

    public function getStartingHuntersNumberOfTruceCycles(): int
    {
        return $this->startingHuntersNumberOfTruceCycles;
    }

    public function setStartingHuntersNumberOfTruceCycles(int $startingHuntersNumberOfTruceCycles): static
    {
        $this->startingHuntersNumberOfTruceCycles = $startingHuntersNumberOfTruceCycles;

        return $this;
    }
}
