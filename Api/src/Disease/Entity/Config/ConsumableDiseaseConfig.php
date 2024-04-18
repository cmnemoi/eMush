<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
#[ORM\Table(name: 'disease_consummable_config')]
class ConsumableDiseaseConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $causeName;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $diseasesName = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $curesName = [];

    // Store the chance (value) for the disease to appear (key)
    #[ORM\Column(type: 'array', nullable: false)]
    private array $diseasesChances;

    // Store the chance (value) for the disease to appear (key)
    #[ORM\Column(type: 'array', nullable: false)]
    private array $curesChances;

    // Store the min delay (value) for the disease to appear (key)
    #[ORM\Column(type: 'array', nullable: false)]
    private array $diseasesDelayMin;

    // Store the max delay (value) for the disease to appear (key)
    #[ORM\Column(type: 'array', nullable: false)]
    private array $diseasesDelayLength;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $effectNumber;

    #[ORM\OneToMany(targetEntity: ConsumableDiseaseAttribute::class, mappedBy: 'consumableDiseaseConfig', cascade: ['persist'])]
    private Collection $consumableAttributes;

    public function __construct()
    {
        $this->consumableAttributes = new ArrayCollection();
        $this->diseasesChances = [];
        $this->curesChances = [];
        $this->diseasesDelayMin = [];
        $this->diseasesDelayLength = [];
        $this->effectNumber = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCauseName(): string
    {
        return $this->causeName;
    }

    public function setCauseName(string $causeName): self
    {
        $this->causeName = $causeName;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function appendConfigKeyToName(string $configName): self
    {
        $this->name = $this->causeName . '_' . $configName;

        return $this;
    }

    public function getDiseasesName(): array
    {
        return $this->diseasesName;
    }

    public function setDiseasesName(array $diseasesName): self
    {
        $this->diseasesName = $diseasesName;

        return $this;
    }

    public function getCuresName(): array
    {
        return $this->curesName;
    }

    public function setCuresName(array $curesName): self
    {
        $this->curesName = $curesName;

        return $this;
    }

    public function getDiseasesChances(): ProbaCollection
    {
        return new ProbaCollection($this->diseasesChances);
    }

    public function setDiseasesChances(array $diseasesChances): self
    {
        $this->diseasesChances = $diseasesChances;

        return $this;
    }

    public function getCuresChances(): ProbaCollection
    {
        return new ProbaCollection($this->curesChances);
    }

    public function setCuresChances(array $curesChances): self
    {
        $this->curesChances = $curesChances;

        return $this;
    }

    public function getDiseasesDelayMin(): ProbaCollection
    {
        return new ProbaCollection($this->diseasesDelayMin);
    }

    public function setDiseasesDelayMin(array $diseasesDelayMin): self
    {
        $this->diseasesDelayMin = $diseasesDelayMin;

        return $this;
    }

    public function getDiseasesDelayLength(): ProbaCollection
    {
        return new ProbaCollection($this->diseasesDelayLength);
    }

    public function setDiseasesDelayLength(array $diseasesDelayLength): self
    {
        $this->diseasesDelayLength = $diseasesDelayLength;

        return $this;
    }

    public function getEffectNumber(): ProbaCollection
    {
        return new ProbaCollection($this->effectNumber);
    }

    public function setEffectNumber(array $effectNumber): self
    {
        $this->effectNumber = $effectNumber;

        return $this;
    }

    public function getAttributes(): Collection
    {
        return $this->consumableAttributes;
    }

    /**
     * @psalm-param ArrayCollection<int, ConsumableDiseaseAttribute>|array<int, ConsumableDiseaseAttribute> $diseases
     */
    public function setAttributes(array|Collection $diseases): self
    {
        if (\is_array($diseases)) {
            $this->consumableAttributes = new ArrayCollection($diseases);

            return $this;
        }

        $this->consumableAttributes = $diseases;

        return $this;
    }

    public function addDisease(ConsumableDiseaseAttribute $disease): self
    {
        if (!$this->consumableAttributes->contains($disease)) {
            $this->consumableAttributes->add($disease);
        }

        return $this;
    }
}
