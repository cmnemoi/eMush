<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Game\Entity\Collection\CharacterConfigCollection;

/**
 * Class Daedalus
 * @package Mush\Game\Entity
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
     * @ORM\OneToMany(targetEntity="Mush\Item\Entity\Item", mappedBy="gameConfig")
     */
    private Collection $itemsConfig;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxPlayer;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
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

    public function setDaedalusConfig(DaedalusConfig $daedalusConfig): GameConfig
    {
        $this->daedalusConfig = $daedalusConfig;
        return $this;
    }

    public function getCharactersConfig(): CharacterConfigCollection
    {
        return new CharacterConfigCollection($this->charactersConfig->toArray());
    }

    public function setCharactersConfig(Collection $charactersConfig): GameConfig
    {
        $this->charactersConfig = $charactersConfig;
        return $this;
    }

    public function getItemsConfig(): Collection
    {
        return $this->itemsConfig;
    }

    public function setItemsConfig(Collection $itemsConfig): GameConfig
    {
        $this->itemsConfig = $itemsConfig;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): GameConfig
    {
        $this->name = $name;
        return $this;
    }

    public function getMaxPlayer(): int
    {
        return $this->maxPlayer;
    }

    public function setMaxPlayer(int $maxPlayer): GameConfig
    {
        $this->maxPlayer = $maxPlayer;
        return $this;
    }

    public function getNbMush(): int
    {
        return $this->nbMush;
    }

    public function setNbMush(int $nbMush): GameConfig
    {
        $this->nbMush = $nbMush;
        return $this;
    }

    public function getCycleLength(): int
    {
        return $this->cycleLength;
    }

    public function getNumberOfCyclePerDay(): int
    {
        return 24 / $this->getCycleLength();
    }

    public function setCycleLength(int $cycleLength): GameConfig
    {
        $this->cycleLength = $cycleLength;
        return $this;
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): GameConfig
    {
        $this->timeZone = $timeZone;
        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): GameConfig
    {
        $this->language = $language;
        return $this;
    }

    public function getInitHealthPoint(): int
    {
        return $this->initHealthPoint;
    }

    public function setInitHealthPoint(int $initHealthPoint): GameConfig
    {
        $this->initHealthPoint = $initHealthPoint;
        return $this;
    }

    public function getMaxHealthPoint(): int
    {
        return $this->maxHealthPoint;
    }

    public function setMaxHealthPoint(int $maxHealthPoint): GameConfig
    {
        $this->maxHealthPoint = $maxHealthPoint;
        return $this;
    }

    public function getInitMoralPoint(): int
    {
        return $this->initMoralPoint;
    }

    public function setInitMoralPoint(int $initMoralPoint): GameConfig
    {
        $this->initMoralPoint = $initMoralPoint;
        return $this;
    }

    public function getMaxMoralPoint(): int
    {
        return $this->maxMoralPoint;
    }

    public function setMaxMoralPoint(int $maxMoralPoint): GameConfig
    {
        $this->maxMoralPoint = $maxMoralPoint;
        return $this;
    }

    public function getInitSatiety(): int
    {
        return $this->initSatiety;
    }

    public function setInitSatiety(int $initSatiety): GameConfig
    {
        $this->initSatiety = $initSatiety;
        return $this;
    }

    public function getInitActionPoint(): int
    {
        return $this->initActionPoint;
    }

    public function setInitActionPoint(int $initActionPoint): GameConfig
    {
        $this->initActionPoint = $initActionPoint;
        return $this;
    }

    public function getMaxActionPoint(): int
    {
        return $this->maxActionPoint;
    }

    public function setMaxActionPoint(int $maxActionPoint): GameConfig
    {
        $this->maxActionPoint = $maxActionPoint;
        return $this;
    }

    public function getInitMovementPoint(): int
    {
        return $this->initMovementPoint;
    }

    public function setInitMovementPoint(int $initMovementPoint): GameConfig
    {
        $this->initMovementPoint = $initMovementPoint;
        return $this;
    }

    public function getMaxMovementPoint(): int
    {
        return $this->maxMovementPoint;
    }

    public function setMaxMovementPoint(int $maxMovementPoint): GameConfig
    {
        $this->maxMovementPoint = $maxMovementPoint;
        return $this;
    }

    public function getMaxItemInInventory(): int
    {
        return $this->maxItemInInventory;
    }

    public function setMaxItemInInventory(int $maxItemInInventory): GameConfig
    {
        $this->maxItemInInventory = $maxItemInInventory;
        return $this;
    }
}
