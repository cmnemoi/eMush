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
    private Collection $diseaseCauseConfigs;

    #[ORM\ManyToMany(targetEntity: DiseaseConfig::class)]
    private Collection $diseaseConfigs;

    #[ORM\ManyToMany(targetEntity: ConsumableDiseaseConfig::class)]
    private Collection $consumableDiseaseConfigs;

    #[ORM\ManyToOne(targetEntity: DifficultyConfig::class)]
    private DifficultyConfig $difficultyConfig;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $name;

    public function __construct()
    {
        $this->characterConfigs = new ArrayCollection();
        $this->equipmentConfigs = new ArrayCollection();
        $this->triumphConfigs = new ArrayCollection();
        $this->diseaseCauseConfigs = new ArrayCollection();
        $this->diseaseConfigs = new ArrayCollection();
        $this->consumableDiseaseConfigs = new ArrayCollection();
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

    public function addCharacterConfig(CharacterConfig $characterConfig): static
    {
        $this->characterConfigs->add($characterConfig);

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

    public function addTriumphConfig(TriumphConfig $triumphConfig): self
    {
        $this->triumphConfigs->add($triumphConfig);

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

    public function addEquipmentConfig(EquipmentConfig $equipmentConfig): static
    {
        $this->equipmentConfigs->add($equipmentConfig);

        return $this;
    }

    public function getDiseaseCauseConfigs(): Collection
    {
        return $this->diseaseCauseConfigs;
    }

    /**
     * @param Collection<int, DiseaseCauseConfig> $diseaseCauseConfigs
     */
    public function setDiseaseCauseConfigs(Collection $diseaseCauseConfigs): static
    {
        $this->diseaseCauseConfigs = $diseaseCauseConfigs;

        return $this;
    }

    public function addDiseaseCauseConfig(DiseaseCauseConfig $diseaseCauseConfig): static
    {
        $this->diseaseCauseConfigs->add($diseaseCauseConfig);

        return $this;
    }

    public function getDiseaseConfigs(): Collection
    {
        return $this->diseaseConfigs;
    }

    public function setDiseaseConfigs(Collection $diseaseConfigs): static
    {
        $this->diseaseConfigs = $diseaseConfigs;

        return $this;
    }

    public function addDiseaseConfig(DiseaseConfig $diseaseConfig): static
    {
        $this->diseaseConfigs->add($diseaseConfig);

        return $this;
    }

    public function getConsumableDiseaseConfigs(): Collection
    {
        return $this->consumableDiseaseConfigs;
    }

    public function setConsumableDiseaseConfigs(Collection $consumableDiseaseConfigs): static
    {
        $this->consumableDiseaseConfigs = $consumableDiseaseConfigs;

        return $this;
    }

    public function addConsumableDiseaseConfig(ConsumableDiseaseConfig $consumableDiseaseConfig): static
    {
        $this->consumableDiseaseConfigs->add($consumableDiseaseConfig);

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
