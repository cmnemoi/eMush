<?php

namespace Mush\Daedalus\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

/**
 * Class Daedalus.
 *
 * @ORM\Entity(repositoryClass="Mush\Daedalus\Repository\DaedalusRepository")
 */
class Daedalus
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
     * @ORM\Column(type="string", nullable=false)
     */
    private string $gameStatus = GameStatusEnum::STARTING;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Place\Entity\Place", mappedBy="daedalus")
     */
    private Collection $places;

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
     * @ORM\Column(type="datetime", nullable=false)
     */
    private DateTime $cycleStartedAt;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->places = new ArrayCollection();
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
    public function setPlayers(Collection $players): Daedalus
    {
        $this->players = $players;

        return $this;
    }

    /**
     * @return static
     */
    public function addPlayer(Player $player): Daedalus
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
    public function removePlayer(Player $player): Daedalus
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
    public function setGameConfig(GameConfig $gameConfig): Daedalus
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    /**
     * @return static
     */
    public function setGameStatus(string $gameStatus): Daedalus
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
    public function setPlaces(Collection $places): Daedalus
    {
        $this->places = $places;

        return $this;
    }

    /**
     * @return static
     */
    public function addPlace(Place $place): Daedalus
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
    public function removePlace(Place $place): Daedalus
    {
        $this->places->removeElement($place);

        return $this;
    }

    public function getOxygen(): int
    {
        return $this->oxygen;
    }

    /**
     * @return static
     */
    public function setOxygen(int $oxygen): Daedalus
    {
        $this->oxygen = $oxygen;

        return $this;
    }

    /**
     * @return static
     */
    public function addOxygen(int $change): Daedalus
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
    public function setFuel(int $fuel): Daedalus
    {
        $this->fuel = $fuel;

        return $this;
    }

    /**
     * @return static
     */
    public function addFuel(int $change): Daedalus
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
    public function addHull(int $change): Daedalus
    {
        $this->hull += $change;

        return $this;
    }

    /**
     * @return static
     */
    public function setHull(int $hull): Daedalus
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
    public function setCycle(int $cycle): Daedalus
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
    public function setDay(int $day): Daedalus
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
    public function setShield(int $shield): Daedalus
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
    public function setSpores(int $spores): Daedalus
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
    public function setDailySpores(int $dailySpores): Daedalus
    {
        $this->dailySpores = $dailySpores;

        return $this;
    }

    public function getFilledAt(): ?DateTime
    {
        return $this->filledAt;
    }

    public function setFilledAt(DateTime $filledAt): Daedalus
    {
        $this->filledAt = $filledAt;

        return $this;
    }

    public function getFinishedAt(): ?DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(DateTime $finishedAt): Daedalus
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getCycleStartedAt(): DateTime
    {
        return $this->cycleStartedAt;
    }

    public function setCycleStartedAt(DateTime $cycleStartedAt): Daedalus
    {
        $this->cycleStartedAt = $cycleStartedAt;

        return $this;
    }
}
