<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
#[ORM\Table(name: 'disease_config')]
class DiseaseConfig implements LogParameterInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $diseaseName;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $type = MedicalConditionTypeEnum::DISEASE;

    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    private Collection $modifierConfigs;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $resistance = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $delayMin = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $delayLength = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $diseasePointMin = 4;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $diseasePointLength = 4;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $override = [];

    public function __construct()
    {
        $this->modifierConfigs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiseaseName(): string
    {
        return $this->diseaseName;
    }

    public function setDiseaseName(string $diseaseName): self
    {
        $this->diseaseName = $diseaseName;

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

    public function buildName(string $configName): self
    {
        $this->name = $this->diseaseName . '_' . $configName;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    /**
     * @param array<int, AbstractModifierConfig>|Collection<int, AbstractModifierConfig> $modifierConfigs
     */
    public function setModifierConfigs(Collection|array $modifierConfigs): self
    {
        if (is_array($modifierConfigs)) {
            $modifierConfigs = new ArrayCollection($modifierConfigs);
        }

        $this->modifierConfigs = $modifierConfigs;

        return $this;
    }

    public function getResistance(): int
    {
        return $this->resistance;
    }

    public function setResistance(int $resistance): self
    {
        $this->resistance = $resistance;

        return $this;
    }

    public function getClassName(): string
    {
        return self::class;
    }

    public function getLogName(): string
    {
        return $this->getDiseaseName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::DISEASE;
    }

    public function getDelayMin(): int
    {
        return $this->delayMin;
    }

    public function setDelayMin(int $delayMin): self
    {
        $this->delayMin = $delayMin;

        return $this;
    }

    public function getDelayLength(): int
    {
        return $this->delayLength;
    }

    public function setDelayLength(int $delayLength): self
    {
        $this->delayLength = $delayLength;

        return $this;
    }

    public function getDiseasePointMin(): int
    {
        return $this->diseasePointMin;
    }

    public function setDiseasePointMin(int $diseasePointMin): self
    {
        $this->diseasePointMin = $diseasePointMin;

        return $this;
    }

    public function getDiseasePointLength(): int
    {
        return $this->diseasePointLength;
    }

    public function setDiseasePointLength(int $diseasePointLength): self
    {
        $this->diseasePointLength = $diseasePointLength;

        return $this;
    }

    public function getOverride(): array
    {
        return $this->override;
    }

    public function setOverride(array $override): self
    {
        $this->override = $override;

        return $this;
    }
}
