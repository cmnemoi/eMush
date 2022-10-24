<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Game\Entity\Collection\TriumphConfigCollection;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;

#[ORM\Entity(repositoryClass: GameConfigRepository::class)]
#[ORM\Table(name: 'config_game')]
class GameConfig
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'gameConfig', targetEntity: DaedalusConfig::class)]
    private DaedalusConfig $daedalusConfig;

    #[ORM\OneToMany(mappedBy: 'gameConfig', targetEntity: CharacterConfig::class)]
    private Collection $charactersConfig;

    #[ORM\OneToMany(mappedBy: 'gameConfig', targetEntity: EquipmentConfig::class)]
    private Collection $equipmentsConfig;

    #[ORM\OneToMany(mappedBy: 'gameConfig', targetEntity: TriumphConfig::class)]
    private Collection $triumphConfig;

    #[ORM\OneToOne(mappedBy: 'gameConfig', targetEntity: DifficultyConfig::class)]
    private DifficultyConfig $difficultyConfig;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $nbMush = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cyclePerGameDay = 8;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycleLength = 0; // in m

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $timeZone;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $language;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxNumberPrivateChannel = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initHealthPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxHealthPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initMoralPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxMoralPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initSatiety = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initActionPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxActionPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initMovementPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxMovementPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxItemInInventory = 0;

    public function __construct()
    {
        $this->charactersConfig = new ArrayCollection();
        $this->equipmentsConfig = new ArrayCollection();
        $this->triumphConfig = new ArrayCollection();
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

    public function setCharactersConfig(Collection $charactersConfig): static
    {
        $this->charactersConfig = $charactersConfig;

        return $this;
    }

    public function getTriumphConfig(): TriumphConfigCollection
    {
        return new TriumphConfigCollection($this->triumphConfig->toArray());
    }

    public function setTriumphConfig(Collection $triumphConfig): self
    {
        $this->triumphConfig = $triumphConfig;

        return $this;
    }

    public function getEquipmentsConfig(): Collection
    {
        return $this->equipmentsConfig;
    }

    public function setEquipmentsConfig(Collection $equipmentsConfig): static
    {
        $this->equipmentsConfig = $equipmentsConfig;

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

    public function getNbMush(): int
    {
        return $this->nbMush;
    }

    public function setNbMush(int $nbMush): static
    {
        $this->nbMush = $nbMush;

        return $this;
    }

    public function getCyclePerGameDay(): int
    {
        return $this->cyclePerGameDay;
    }

    public function setCyclePerGameDay(int $cyclePerGameDay): static
    {
        $this->cyclePerGameDay = $cyclePerGameDay;

        return $this;
    }

    public function getCycleLength(): int
    {
        return $this->cycleLength;
    }

    public function setCycleLength(int $cycleLength): static
    {
        $this->cycleLength = $cycleLength;

        return $this;
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): static
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    public function getMaxNumberPrivateChannel(): int
    {
        return $this->maxNumberPrivateChannel;
    }

    public function setMaxNumberPrivateChannel(int $maxNumberPrivateChannel): static
    {
        $this->maxNumberPrivateChannel = $maxNumberPrivateChannel;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getInitHealthPoint(): int
    {
        return $this->initHealthPoint;
    }

    public function setInitHealthPoint(int $initHealthPoint): static
    {
        $this->initHealthPoint = $initHealthPoint;

        return $this;
    }

    public function getMaxHealthPoint(): int
    {
        return $this->maxHealthPoint;
    }

    public function setMaxHealthPoint(int $maxHealthPoint): static
    {
        $this->maxHealthPoint = $maxHealthPoint;

        return $this;
    }

    public function getInitMoralPoint(): int
    {
        return $this->initMoralPoint;
    }

    public function setInitMoralPoint(int $initMoralPoint): static
    {
        $this->initMoralPoint = $initMoralPoint;

        return $this;
    }

    public function getMaxMoralPoint(): int
    {
        return $this->maxMoralPoint;
    }

    public function setMaxMoralPoint(int $maxMoralPoint): static
    {
        $this->maxMoralPoint = $maxMoralPoint;

        return $this;
    }

    public function getInitSatiety(): int
    {
        return $this->initSatiety;
    }

    public function setInitSatiety(int $initSatiety): static
    {
        $this->initSatiety = $initSatiety;

        return $this;
    }

    public function getInitActionPoint(): int
    {
        return $this->initActionPoint;
    }

    public function setInitActionPoint(int $initActionPoint): static
    {
        $this->initActionPoint = $initActionPoint;

        return $this;
    }

    public function getMaxActionPoint(): int
    {
        return $this->maxActionPoint;
    }

    public function setMaxActionPoint(int $maxActionPoint): static
    {
        $this->maxActionPoint = $maxActionPoint;

        return $this;
    }

    public function getInitMovementPoint(): int
    {
        return $this->initMovementPoint;
    }

    public function setInitMovementPoint(int $initMovementPoint): static
    {
        $this->initMovementPoint = $initMovementPoint;

        return $this;
    }

    public function getMaxMovementPoint(): int
    {
        return $this->maxMovementPoint;
    }

    public function setMaxMovementPoint(int $maxMovementPoint): static
    {
        $this->maxMovementPoint = $maxMovementPoint;

        return $this;
    }

    public function getMaxItemInInventory(): int
    {
        return $this->maxItemInInventory;
    }

    public function setMaxItemInInventory(int $maxItemInInventory): static
    {
        $this->maxItemInInventory = $maxItemInInventory;

        return $this;
    }
}
