<?php

namespace Mush\Disease\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;

/**
 * @ORM\Entity
 * @ORM\Table(name="disease_consummable_config")
 */
class ConsumableDiseaseConfig
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
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesName = [];

    //Store the chance (value) for the disease to appear (key)
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesChances = [];

    //Store the min delay (value) for the disease to appear (key)
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesDelayMin = [];

    //Store the max delay (value) for the disease to appear (key)
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesDelayLength = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $fruitEffectsNumber = [];

    /**
     * @ORM\OneToMany(targetEntity="Mush\Disease\Entity\ConsumableDiseaseCharacteristic", mappedBy="consumableDiseaseConfig", cascade={"persist"})
     */
    private Collection $diseases;

    public function __construct()
    {
        $this->diseases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): ConsumableDiseaseConfig
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ConsumableDiseaseConfig
    {
        $this->name = $name;

        return $this;
    }

    public function getDiseasesName(): array
    {
        return $this->diseasesName;
    }

    public function setDiseasesName(array $diseasesName): ConsumableDiseaseConfig
    {
        $this->diseasesName = $diseasesName;

        return $this;
    }

    public function getDiseasesChances(): array
    {
        return $this->diseasesChances;
    }

    public function setDiseasesChances(array $diseasesChances): ConsumableDiseaseConfig
    {
        $this->diseasesChances = $diseasesChances;

        return $this;
    }

    public function getDiseasesDelayMin(): array
    {
        return $this->diseasesDelayMin;
    }

    public function setDiseasesDelayMin(array $diseasesDelayMin): ConsumableDiseaseConfig
    {
        $this->diseasesDelayMin = $diseasesDelayMin;

        return $this;
    }

    public function getDiseasesDelayLength(): array
    {
        return $this->diseasesDelayLength;
    }

    public function setDiseasesDelayLength(array $diseasesDelayLength): ConsumableDiseaseConfig
    {
        $this->diseasesDelayLength = $diseasesDelayLength;

        return $this;
    }

    public function getFruitEffectsNumber(): array
    {
        return $this->fruitEffectsNumber;
    }

    public function setFruitEffectsNumber(array $fruitEffectsNumber): ConsumableDiseaseConfig
    {
        $this->fruitEffectsNumber = $fruitEffectsNumber;

        return $this;
    }

    public function getDiseases(): Collection
    {
        return $this->diseases;
    }

    public function setDiseases(Collection $diseases): ConsumableDiseaseConfig
    {
        $this->diseases = $diseases;

        return $this;
    }

    public function addDisease(ConsumableDiseaseCharacteristic $disease): ConsumableDiseaseConfig
    {
        if (!$this->diseases->contains($disease)) {
            $this->diseases->add($disease);
        }

        return $this;
    }
}
