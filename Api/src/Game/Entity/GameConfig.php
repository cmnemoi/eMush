<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Game\Entity\Collection\CharacterConfigCollection;
use Mush\Game\Entity\Collection\TriumphConfigCollection;

/**
 * Class Daedalus.
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
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $nbMush;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $cycleLength;

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
    private int $maxNumberPrivateChannel;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initHealthPoint;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxHealthPoint;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initMoralPoint;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxMoralPoint;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initSatiety;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initActionPoint;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxActionPoint;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $initMovementPoint;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxMovementPoint;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxItemInInventory;

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
     * @return float|int
     */
    public function getNumberOfCyclePerDay()
    {
        return 24 / $this->getCycleLength();
    }

    /**
     * @return static
     */
    public function setCycleLength(int $cycleLength): GameConfig
    {
        $this->cycleLength = $cycleLength;

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
}
