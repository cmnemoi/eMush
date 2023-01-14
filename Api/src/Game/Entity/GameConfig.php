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
    private Collection $characterConfigs;

    #[ORM\ManyToMany(targetEntity: EquipmentConfig::class)]
    private Collection $equipmentConfigs;

    #[ORM\ManyToMany(targetEntity: StatusConfig::class)]
    private Collection $statusConfigs;

    #[ORM\ManyToMany(targetEntity: TriumphConfig::class)]
    private Collection $triumphConfigs;

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
        $this->characterConfigs = new ArrayCollection();
        $this->equipmentConfigs = new ArrayCollection();
        $this->triumphConfigs = new ArrayCollection();
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

    public function getCharacterConfigs(): CharacterConfigCollection
    {
        return new CharacterConfigCollection($this->characterConfigs->toArray());
    }

    /**
     * @param Collection<int, CharacterConfig> $characterConfigs
     */
    public function setCharacterConfigs(Collection|array $characterConfigs): static
    {
        if (is_array($characterConfigs)) {
            $characterConfigs = new ArrayCollection($characterConfigs);
        }

        $this->characterConfigs = $characterConfigs;

        return $this;
    }

    public function addCharacterConfig(CharacterConfig $characterConfigs): static
    {
        $this->characterConfigs->add($characterConfigs);

        return $this;
    }

    public function getTriumphConfigs(): TriumphConfigCollection
    {
        return new TriumphConfigCollection($this->triumphConfigs->toArray());
    }

    /**
     * @param Collection<int, TriumphConfig> $triumphConfigs
     */
    public function setTriumphConfigs(Collection $triumphConfigs): self
    {
        $this->triumphConfigs = $triumphConfigs;

        return $this;
    }

    public function addTriumphConfig(TriumphConfig $triumphConfigs): self
    {
        $this->triumphConfigs->add($triumphConfigs);

        return $this;
    }

    public function getEquipmentConfigs(): Collection
    {
        return $this->equipmentConfigs;
    }

    public function setEquipmentConfigs(Collection|array $equipmentConfigs): static
    {
        if (is_array($equipmentConfigs)) {
            $equipmentConfigs = new ArrayCollection($equipmentConfigs);
        }

        $this->equipmentConfigs = $equipmentConfigs;

        return $this;
    }

    public function addEquipmentConfig(EquipmentConfig $equipmentConfigs): static
    {
        $this->equipmentConfigs->add($equipmentConfigs);

        return $this;
    }

    public function getDiseaseCauseConfig(): Collection
    {
        return $this->diseaseCauseConfig;
    }

    /**
     * @param Collection<int, DiseaseCauseConfig> $diseaseCauseConfig
     */
    public function setDiseaseCauseConfig(Collection $diseaseCauseConfig): static
    {
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

    public function setDiseaseConfig(Collection $diseaseConfig): static
    {
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

    public function setConsumableDiseaseConfig(Collection $consumableDiseaseConfig): static
    {
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
     * @param Collection<int, StatusConfig> $statusConfigs
     */
    public function setStatusConfigs(Collection|array $statusConfigs): static
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
        return $this->characterConfigs->count();
    }
}
