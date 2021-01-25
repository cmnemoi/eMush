<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Game\Entity\Collection\CharacterConfigCollection;
use Mush\Game\Entity\Collection\TriumphConfigCollection;

/**
 * Class GameConfig.
 *
 * @ORM\Entity(repositoryClass="Mush\Game\Repository\GameConfigRepository")
 * @ORM\Table(name="config_game")
 */
class GameConfig
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToOne (targetEntity="Mush\Daedalus\Entity\DaedalusConfig", mappedBy="gameConfig")
     */
    private DaedalusConfig $daedalusConfig;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Game\Entity\CharacterConfig", mappedBy="gameConfig")
     */
    private Collection $charactersConfig;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Equipment\Entity\EquipmentConfig", mappedBy="gameConfig")
     */
    private Collection $equipmentsConfig;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Game\Entity\TriumphConfig", mappedBy="gameConfig")
     */
    private Collection $triumphConfig;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Game\Entity\DifficultyConfig", mappedBy="gameConfig")
     */
    private DifficultyConfig $difficultyConfig;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $nbMush = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $cycleLength = 0; //in h

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $dayLength = 0; //in cycles

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $timeZone;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $language;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maxNumberPrivateChannel = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initHealthPoint = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxHealthPoint = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initMoralPoint = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxMoralPoint = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initSatiety = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initActionPoint = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxActionPoint = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initMovementPoint = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxMovementPoint = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxItemInInventory = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxOxygen = 0;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxFuel = 0;

    public function __construct()
    {
        $this->charactersConfig = new ArrayCollection();
        $this->equipmentsConfig = new ArrayCollection();
        $this->triumphConfig = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalusConfig(): DaedalusConfig
    {
        return $this->daedalusConfig;
    }

    /**
     * @return static
     */
    public function setDaedalusConfig(DaedalusConfig $daedalusConfig): GameConfig
    {
        $this->daedalusConfig = $daedalusConfig;

        return $this;
    }

    public function getCharactersConfig(): CharacterConfigCollection
    {
        return new CharacterConfigCollection($this->charactersConfig->toArray());
    }

    /**
     * @return static
     */
    public function setCharactersConfig(Collection $charactersConfig): GameConfig
    {
        $this->charactersConfig = $charactersConfig;

        return $this;
    }

    public function getTriumphConfig(): TriumphConfigCollection
    {
        return new TriumphConfigCollection($this->triumphConfig->toArray());
    }

    public function setTriumphConfig(Collection $triumphConfig): GameConfig
    {
        $this->triumphConfig = $triumphConfig;

        return $this;
    }

    public function getEquipmentsConfig(): Collection
    {
        return $this->equipmentsConfig;
    }

    /**
     * @return static
     */
    public function setEquipmentsConfig(Collection $equipmentsConfig): GameConfig
    {
        $this->equipmentsConfig = $equipmentsConfig;

        return $this;
    }

    public function getDifficultyConfig(): DifficultyConfig
    {
        return $this->difficultyConfig;
    }

    /**
     * @return static
     */
    public function setDifficultyConfig(DifficultyConfig $difficultyConfig): GameConfig
    {
        $this->difficultyConfig = $difficultyConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return static
     */
    public function setName(string $name): GameConfig
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

    /**
     * @return static
     */
    public function setNbMush(int $nbMush): GameConfig
    {
        $this->nbMush = $nbMush;

        return $this;
    }

    public function getCycleLength(): int
    {
        return $this->cycleLength;
    }

    /**
     * @return static
     */
    public function setCycleLength(int $cycleLength): GameConfig
    {
        $this->cycleLength = $cycleLength;

        return $this;
    }

    public function getDayLength(): int
    {
        return $this->dayLength;
    }

    /**
     * @return static
     */
    public function setDayLength(int $dayLength): GameConfig
    {
        $this->dayLength = $dayLength;

        return $this;
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    /**
     * @return static
     */
    public function setTimeZone(string $timeZone): GameConfig
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    public function getMaxNumberPrivateChannel(): int
    {
        return $this->maxNumberPrivateChannel;
    }

    /**
     * @return static
     */
    public function setMaxNumberPrivateChannel(int $maxNumberPrivateChannel): GameConfig
    {
        $this->maxNumberPrivateChannel = $maxNumberPrivateChannel;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return static
     */
    public function setLanguage(string $language): GameConfig
    {
        $this->language = $language;

        return $this;
    }

    public function getInitHealthPoint(): int
    {
        return $this->initHealthPoint;
    }

    /**
     * @return static
     */
    public function setInitHealthPoint(int $initHealthPoint): GameConfig
    {
        $this->initHealthPoint = $initHealthPoint;

        return $this;
    }

    public function getMaxHealthPoint(): int
    {
        return $this->maxHealthPoint;
    }

    /**
     * @return static
     */
    public function setMaxHealthPoint(int $maxHealthPoint): GameConfig
    {
        $this->maxHealthPoint = $maxHealthPoint;

        return $this;
    }

    public function getInitMoralPoint(): int
    {
        return $this->initMoralPoint;
    }

    /**
     * @return static
     */
    public function setInitMoralPoint(int $initMoralPoint): GameConfig
    {
        $this->initMoralPoint = $initMoralPoint;

        return $this;
    }

    public function getMaxMoralPoint(): int
    {
        return $this->maxMoralPoint;
    }

    /**
     * @return static
     */
    public function setMaxMoralPoint(int $maxMoralPoint): GameConfig
    {
        $this->maxMoralPoint = $maxMoralPoint;

        return $this;
    }

    public function getInitSatiety(): int
    {
        return $this->initSatiety;
    }

    /**
     * @return static
     */
    public function setInitSatiety(int $initSatiety): GameConfig
    {
        $this->initSatiety = $initSatiety;

        return $this;
    }

    public function getInitActionPoint(): int
    {
        return $this->initActionPoint;
    }

    /**
     * @return static
     */
    public function setInitActionPoint(int $initActionPoint): GameConfig
    {
        $this->initActionPoint = $initActionPoint;

        return $this;
    }

    public function getMaxActionPoint(): int
    {
        return $this->maxActionPoint;
    }

    /**
     * @return static
     */
    public function setMaxActionPoint(int $maxActionPoint): GameConfig
    {
        $this->maxActionPoint = $maxActionPoint;

        return $this;
    }

    public function getInitMovementPoint(): int
    {
        return $this->initMovementPoint;
    }

    /**
     * @return static
     */
    public function setInitMovementPoint(int $initMovementPoint): GameConfig
    {
        $this->initMovementPoint = $initMovementPoint;

        return $this;
    }

    public function getMaxMovementPoint(): int
    {
        return $this->maxMovementPoint;
    }

    /**
     * @return static
     */
    public function setMaxMovementPoint(int $maxMovementPoint): GameConfig
    {
        $this->maxMovementPoint = $maxMovementPoint;

        return $this;
    }

    public function getMaxItemInInventory(): int
    {
        return $this->maxItemInInventory;
    }

    /**
     * @return static
     */
    public function setMaxItemInInventory(int $maxItemInInventory): GameConfig
    {
        $this->maxItemInInventory = $maxItemInInventory;

        return $this;
    }

    public function getMaxOxygen(): int
    {
        return $this->maxOxygen;
    }

    /**
     * @return static
     */
    public function setMaxOxygen(int $maxOxygen): GameConfig
    {
        $this->maxOxygen = $maxOxygen;

        return $this;
    }

    public function getMaxFuel(): int
    {
        return $this->maxFuel;
    }

    /**
     * @return static
     */
    public function setMaxFuel(int $maxFuel): GameConfig
    {
        $this->maxFuel = $maxFuel;

        return $this;
    }
}
