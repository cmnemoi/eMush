<?php

namespace Mush\Daedalus\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

/**
 * Class Daedalus.
 *
 * @ORM\Entity(repositoryClass="Mush\Daedalus\Repository\DaedalusRepository")
 */
class Daedalus implements ModifierHolder
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Player\Entity\Player", mappedBy="daedalus")
     */
    private Collection $players;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Game\Entity\GameConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Daedalus\Entity\Neron", inversedBy="daedalus")
     */
    private Neron $neron;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $gameStatus = GameStatusEnum::STANDBY;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Place\Entity\Place", mappedBy="daedalus")
     */
    private Collection $places;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Modifier\Entity\Modifier", mappedBy="daedalus")
     */
    private Collection $modifiers;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $oxygen = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $fuel = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $hull = 100;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $day = 1;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cycle = 1;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $shield = -2;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $spores = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $dailySpores = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $filledAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $finishedAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $cycleStartedAt = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isCycleChange = false;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->places = new ArrayCollection();
        $this->modifiers = new ModifierCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayers(): PlayerCollection
    {
        return new PlayerCollection($this->players->toArray());
    }

    /**
     * @return static
     */
    public function setPlayers(Collection $players): self
    {
        $this->players = $players;

        return $this;
    }

    /**
     * @return static
     */
    public function addPlayer(Player $player): self
    {
        if (!$this->getPlayers()->contains($player)) {
            $this->players->add($player);

            $player->setDaedalus($this);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removePlayer(Player $player): self
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    /**
     * @return static
     */
    public function setGameConfig(GameConfig $gameConfig): self
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getNeron(): Neron
    {
        return $this->neron;
    }

    public function setNeron(Neron $neron): self
    {
        $this->neron = $neron;

        return $this;
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    /**
     * @return static
     */
    public function setGameStatus(string $gameStatus): self
    {
        $this->gameStatus = $gameStatus;

        return $this;
    }

    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function getRooms(): Collection
    {
        return $this->getPlaces()->filter(fn (Place $place) => $place->getType() === PlaceTypeEnum::ROOM);
    }

    public function getPlaceByName(string $name): ?Place
    {
        $place = $this->getPlaces()->filter(fn (Place $place) => $place->getName() === $name)->first();

        return $place === false ? null : $place;
    }

    /**
     * @return static
     */
    public function setPlaces(Collection $places): self
    {
        $this->places = $places;

        return $this;
    }

    /**
     * @return static
     */
    public function addPlace(Place $place): self
    {
        if (!$this->getPlaces()->contains($place)) {
            $this->places->add($place);

            $place->setDaedalus($this);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removePlace(Place $place): self
    {
        $this->places->removeElement($place);

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    public function getAllModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    /**
     * @return static
     */
    public function addModifier(Modifier $modifier): self
    {
        $this->modifiers->add($modifier);

        return $this;
    }

    public function getOxygen(): int
    {
        return $this->oxygen;
    }

    /**
     * @return static
     */
    public function setOxygen(int $oxygen): self
    {
        $this->oxygen = $oxygen;

        return $this;
    }

    /**
     * @return static
     */
    public function addOxygen(int $change): self
    {
        $this->oxygen += $change;

        return $this;
    }

    public function getFuel(): int
    {
        return $this->fuel;
    }

    /**
     * @return static
     */
    public function setFuel(int $fuel): self
    {
        $this->fuel = $fuel;

        return $this;
    }

    /**
     * @return static
     */
    public function addFuel(int $change): self
    {
        $this->fuel += $change;

        return $this;
    }

    public function getHull(): int
    {
        return $this->hull;
    }

    /**
     * @return static
     */
    public function addHull(int $change): self
    {
        $this->hull += $change;

        return $this;
    }

    /**
     * @return static
     */
    public function setHull(int $hull): self
    {
        $this->hull = $hull;

        return $this;
    }

    public function getCycle(): int
    {
        return $this->cycle;
    }

    /**
     * @return static
     */
    public function setCycle(int $cycle): self
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * @return static
     */
    public function setDay(int $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getShield(): int
    {
        return $this->shield;
    }

    /**
     * @return static
     */
    public function setShield(int $shield): self
    {
        $this->shield = $shield;

        return $this;
    }

    public function getSpores(): int
    {
        return $this->spores;
    }

    /**
     * @return static
     */
    public function setSpores(int $spores): self
    {
        $this->spores = $spores;

        return $this;
    }

    public function getDailySpores(): int
    {
        return $this->dailySpores;
    }

    /**
     * @return static
     */
    public function setDailySpores(int $dailySpores): self
    {
        $this->dailySpores = $dailySpores;

        return $this;
    }

    public function getFilledAt(): ?DateTime
    {
        return $this->filledAt;
    }

    public function setFilledAt(DateTime $filledAt): self
    {
        $this->filledAt = $filledAt;

        return $this;
    }

    public function getFinishedAt(): ?DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(DateTime $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getCycleStartedAt(): ?DateTime
    {
        return $this->cycleStartedAt;
    }

    public function setCycleStartedAt(DateTime $cycleStartedAt): self
    {
        $this->cycleStartedAt = $cycleStartedAt;

        return $this;
    }

    public function isCycleChange(): bool
    {
        return $this->isCycleChange;
    }

    public function setIsCycleChange(bool $isCycleChange): self
    {
        $this->isCycleChange = $isCycleChange;

        return $this;
    }

    public function getClassName(): string
    {
        return get_class($this);
    }
}
