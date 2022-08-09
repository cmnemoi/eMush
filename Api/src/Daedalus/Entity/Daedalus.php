<?php

namespace Mush\Daedalus\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

#[ORM\Entity(repositoryClass: DaedalusRepository::class)]
#[ORM\Table(name: 'daedalus')]
class Daedalus implements ModifierHolder
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: Player::class)]
    private Collection $players;

    #[ORM\ManyToOne(targetEntity: GameConfig::class)]
    private GameConfig $gameConfig;

    #[ORM\OneToOne(inversedBy: 'daedalus', targetEntity: Neron::class)]
    private Neron $neron;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $gameStatus = GameStatusEnum::STANDBY;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: Place::class)]
    private Collection $places;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: Modifier::class)]
    private Collection $modifiers;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $oxygen = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $fuel = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $hull = 100;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $day = 1;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycle = 1;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $shield = -2;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $spores = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $dailySpores = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $filledAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $finishedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $cycleStartedAt = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
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

    public function setPlayers(Collection $players): static
    {
        $this->players = $players;

        return $this;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->getPlayers()->contains($player)) {
            $this->players->add($player);

            $player->setDaedalus($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): static
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getNeron(): Neron
    {
        return $this->neron;
    }

    public function setNeron(Neron $neron): static
    {
        $this->neron = $neron;

        return $this;
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    public function setGameStatus(string $gameStatus): static
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

    public function setPlaces(Collection $places): static
    {
        $this->places = $places;

        return $this;
    }

    public function addPlace(Place $place): static
    {
        if (!$this->getPlaces()->contains($place)) {
            $this->places->add($place);

            $place->setDaedalus($this);
        }

        return $this;
    }

    public function removePlace(Place $place): static
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

    public function addModifier(Modifier $modifier): static
    {
        $this->modifiers->add($modifier);

        return $this;
    }

    public function getOxygen(): int
    {
        return $this->oxygen;
    }

    public function setOxygen(int $oxygen): static
    {
        $this->oxygen = $oxygen;

        return $this;
    }

    public function addOxygen(int $change): static
    {
        $this->oxygen += $change;

        return $this;
    }

    public function getFuel(): int
    {
        return $this->fuel;
    }

    public function setFuel(int $fuel): static
    {
        $this->fuel = $fuel;

        return $this;
    }

    public function addFuel(int $change): static
    {
        $this->fuel += $change;

        return $this;
    }

    public function getHull(): int
    {
        return $this->hull;
    }

    public function addHull(int $change): static
    {
        $this->hull += $change;

        return $this;
    }

    public function setHull(int $hull): static
    {
        $this->hull = $hull;

        return $this;
    }

    public function getCycle(): int
    {
        return $this->cycle;
    }

    public function setCycle(int $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function setDay(int $day): static
    {
        $this->day = $day;

        return $this;
    }

    public function getShield(): int
    {
        return $this->shield;
    }

    public function setShield(int $shield): static
    {
        $this->shield = $shield;

        return $this;
    }

    public function getSpores(): int
    {
        return $this->spores;
    }

    public function setSpores(int $spores): static
    {
        $this->spores = $spores;

        return $this;
    }

    public function getDailySpores(): int
    {
        return $this->dailySpores;
    }

    public function setDailySpores(int $dailySpores): static
    {
        $this->dailySpores = $dailySpores;

        return $this;
    }

    public function getFilledAt(): ?DateTime
    {
        return $this->filledAt;
    }

    public function setFilledAt(DateTime $filledAt): static
    {
        $this->filledAt = $filledAt;

        return $this;
    }

    public function getFinishedAt(): ?DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(DateTime $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getCycleStartedAt(): ?DateTime
    {
        return $this->cycleStartedAt;
    }

    public function setCycleStartedAt(DateTime $cycleStartedAt): static
    {
        $this->cycleStartedAt = $cycleStartedAt;

        return $this;
    }

    public function isCycleChange(): bool
    {
        return $this->isCycleChange;
    }

    public function setIsCycleChange(bool $isCycleChange): static
    {
        $this->isCycleChange = $isCycleChange;

        return $this;
    }

    public function getClassName(): string
    {
        return get_class($this);
    }
}
