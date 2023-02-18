<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Disease\Entity\Config\ConsumableDiseaseConfig;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Game\Entity\Collection\TriumphConfigCollection;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity(repositoryClass: GameConfigRepository::class)]
#[ORM\Table(name: 'config_game')]
class GameConfig
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DaedalusConfig::class)]
    private DaedalusConfig $daedalusConfig;

    #[ORM\ManyToMany(targetEntity: CharacterConfig::class)]
    private Collection $charactersConfig;

    #[ORM\ManyToMany(targetEntity: EquipmentConfig::class)]
    private Collection $equipmentsConfig;

    #[ORM\ManyToMany(targetEntity: StatusConfig::class)]
    private Collection $statusConfigs;

    #[ORM\ManyToMany(targetEntity: TriumphConfig::class)]
    private Collection $triumphConfig;

    #[ORM\ManyToMany(targetEntity: DiseaseCauseConfig::class)]
    private Collection $diseaseCauseConfig;

    #[ORM\ManyToMany(targetEntity: DiseaseConfig::class)]
    private Collection $diseaseConfig;

    #[ORM\ManyToMany(targetEntity: ConsumableDiseaseConfig::class)]
    private Collection $consumableDiseaseConfig;

    #[ORM\ManyToOne(targetEntity: DifficultyConfig::class)]
    private DifficultyConfig $difficultyConfig;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $name;

    public function __construct()
    {
        $this->charactersConfig = new ArrayCollection();
        $this->equipmentsConfig = new ArrayCollection();
        $this->triumphConfig = new ArrayCollection();
        $this->diseaseCauseConfig = new ArrayCollection();
        $this->diseaseConfig = new ArrayCollection();
        $this->consumableDiseaseConfig = new ArrayCollection();
        $this->statusConfigs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalusConfig(): DaedalusConfig
    {
        return $this->daedalusConfig;
    }

    public function setDaedalusConfig(DaedalusConfig $daedalusConfig): static
    {
        $this->daedalusConfig = $daedalusConfig;

        return $this;
    }

    public function getCharactersConfig(): CharacterConfigCollection
    {
        return new CharacterConfigCollection($this->charactersConfig->toArray());
    }

    /**
     * @psalm-param ArrayCollection<int, CharacterConfig> $charactersConfig
     */
    public function setCharactersConfig(ArrayCollection|array $charactersConfig): static
    {
        if (is_array($charactersConfig)) {
            $charactersConfig = new ArrayCollection($charactersConfig);
        }

        $this->charactersConfig = $charactersConfig;

        return $this;
    }

    public function addCharactersConfig(CharacterConfig $charactersConfig): static
    {
        $this->charactersConfig->add($charactersConfig);

        return $this;
    }

    public function getTriumphConfig(): TriumphConfigCollection
    {
        return new TriumphConfigCollection($this->triumphConfig->toArray());
    }

    /**
     * @psalm-param ArrayCollection<int, TriumphConfig> $triumphConfig
     */
    public function setTriumphConfig(ArrayCollection|array $triumphConfig): self
    {
        if (is_array($triumphConfig)) {
            $triumphConfig = new ArrayCollection($triumphConfig);
        }

        $this->triumphConfig = $triumphConfig;

        return $this;
    }

    public function addTriumphConfig(TriumphConfig $triumphConfig): self
    {
        $this->triumphConfig->add($triumphConfig);

        return $this;
    }

    public function getEquipmentsConfig(): Collection
    {
        return $this->equipmentsConfig;
    }

    /**
     * @psalm-param ArrayCollection<int, EquipmentConfig> $equipmentsConfig
     */
    public function setEquipmentsConfig(ArrayCollection|array $equipmentsConfig): static
    {
        if (is_array($equipmentsConfig)) {
            $equipmentsConfig = new ArrayCollection($equipmentsConfig);
        }

        $this->equipmentsConfig = $equipmentsConfig;

        return $this;
    }

    public function addEquipmentConfig(EquipmentConfig $equipmentsConfig): static
    {
        $this->equipmentsConfig->add($equipmentsConfig);

        return $this;
    }

    public function getDiseaseCauseConfig(): Collection
    {
        return $this->diseaseCauseConfig;
    }

    /**
     * @psalm-param ArrayCollection<int, DiseaseCauseConfig> $diseaseCauseConfig
     */
    public function setDiseaseCauseConfig(ArrayCollection|array $diseaseCauseConfig): static
    {
        if (is_array($diseaseCauseConfig)) {
            $diseaseCauseConfig = new ArrayCollection($diseaseCauseConfig);
        }

        $this->diseaseCauseConfig = $diseaseCauseConfig;

        return $this;
    }

    public function addDiseaseCauseConfig(DiseaseCauseConfig $diseaseCauseConfig): static
    {
        $this->diseaseCauseConfig->add($diseaseCauseConfig);

        return $this;
    }

    public function getDiseaseConfig(): Collection
    {
        return $this->diseaseConfig;
    }

    /**
     * @psalm-param ArrayCollection<int, DiseaseConfig> $diseaseConfig
     */
    public function setDiseaseConfig(ArrayCollection|array $diseaseConfig): static
    {
        if (is_array($diseaseConfig)) {
            $diseaseConfig = new ArrayCollection($diseaseConfig);
        }

        $this->diseaseConfig = $diseaseConfig;

        return $this;
    }

    public function addDiseaseConfig(DiseaseConfig $diseaseConfig): static
    {
        $this->diseaseConfig->add($diseaseConfig);

        return $this;
    }

    public function getConsumableDiseaseConfig(): Collection
    {
        return $this->consumableDiseaseConfig;
    }

    /**
     * @psalm-param ArrayCollection<int, ConsumableDiseaseConfig> $consumableDiseaseConfig
     */
    public function setConsumableDiseaseConfig(ArrayCollection|array $consumableDiseaseConfig): static
    {
        if (is_array($consumableDiseaseConfig)) {
            $consumableDiseaseConfig = new ArrayCollection($consumableDiseaseConfig);
        }

        $this->consumableDiseaseConfig = $consumableDiseaseConfig;

        return $this;
    }

    public function addConsumableDiseaseConfig(ConsumableDiseaseConfig $consumableDiseaseConfig): static
    {
        $this->consumableDiseaseConfig->add($consumableDiseaseConfig);

        return $this;
    }

    public function getDifficultyConfig(): DifficultyConfig
    {
        return $this->difficultyConfig;
    }

    public function setDifficultyConfig(DifficultyConfig $difficultyConfig): static
    {
        $this->difficultyConfig = $difficultyConfig;

        return $this;
    }

    public function addStatusConfig(StatusConfig $statusConfig): static
    {
        $this->statusConfigs->add($statusConfig);

        return $this;
    }

    public function getStatusConfigs(): Collection
    {
        return $this->statusConfigs;
    }

    /**
     * @psalm-param ArrayCollection<int, StatusConfig> $statusConfigs
     */
    public function setStatusConfigs(ArrayCollection|array $statusConfigs): static
    {
        if (is_array($statusConfigs)) {
            $statusConfigs = new ArrayCollection($statusConfigs);
        }

        $this->statusConfigs = $statusConfigs;

        return $this;
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

    public function getMaxPlayer(): int
    {
        return $this->charactersConfig->count();
    }
}
