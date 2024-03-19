<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\SpaceCoordinates;
use Mush\Exploration\Enum\SpaceOrientationEnum;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Entity\HunterTargetEntityInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\PlayerStatusEnum;

#[ORM\Entity(repositoryClass: DaedalusRepository::class)]
#[ORM\Table(name: 'daedalus')]
class Daedalus implements ModifierHolderInterface, GameVariableHolderInterface, HunterTargetEntityInterface, StatusHolderInterface
{
    use TimestampableEntity;
    use TargetStatusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(mappedBy: 'daedalus', targetEntity: DaedalusInfo::class)]
    private DaedalusInfo $daedalusInfo;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: Player::class)]
    private Collection $players;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: Place::class)]
    private Collection $places;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: GameModifier::class, cascade: ['REMOVE'])]
    private Collection $modifiers;

    #[ORM\OneToOne(targetEntity: GameVariableCollection::class, cascade: ['ALL'])]
    private DaedalusVariables $daedalusVariables;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: StatusTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $day = 1;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycle = 1;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $filledAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $finishedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $cycleStartedAt = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isCycleChange = false;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $dailyActionPointsSpent = 0;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => SpaceOrientationEnum::NORTH])]
    private string $orientation = SpaceOrientationEnum::NORTH;

    #[ORM\OneToOne(targetEntity: Exploration::class)]
    private ?Exploration $exploration = null;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->places = new ArrayCollection();
        $this->modifiers = new ModifierCollection();
        $this->statuses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalusInfo;
    }

    public function setDaedalusInfo(DaedalusInfo $daedalusInfo): static
    {
        $this->daedalusInfo = $daedalusInfo;

        return $this;
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

    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function getRooms(): Collection
    {
        return $this->getPlaces()->filter(fn (Place $place) => $place->getType() === PlaceTypeEnum::ROOM);
    }

    public function getSpace(): Place
    {
        $space = $this->getPlaces()->filter(fn (Place $place) => $place->getName() === RoomEnum::SPACE)->first();
        if (!$space) {
            throw new \Exception('Daedalus should have a place named Space');
        }

        return $space;
    }

    public function getPlanetPlace(): Place
    {
        $planetPlace = $place = $this->getPlaces()->filter(fn (Place $place) => $place->getName() === RoomEnum::PLANET)->first();
        if (!$planetPlace) {
            throw new \Exception('Daedalus should have a planet place');
        }

        return $planetPlace;
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

    public function addModifier(GameModifier $modifier): static
    {
        $this->modifiers->add($modifier);

        return $this;
    }

    public function getAttackingHunters(): HunterCollection
    {
        return $this->getSpace()->getAttackingHunters();
    }

    public function getHunterPool(): HunterCollection
    {
        return $this->getSpace()->getHunterPool();
    }

    public function setHunters(ArrayCollection $hunters): static
    {
        $this->getSpace()->setHunters($hunters);

        return $this;
    }

    public function addHunter(Hunter $hunter): static
    {
        $this->getSpace()->addHunter($hunter);

        return $this;
    }

    public function removeHunter(Hunter $hunter): static
    {
        $this->getSpace()->removeHunter($hunter);

        return $this;
    }

    public function getVariableByName(string $variableName): GameVariable
    {
        return $this->daedalusVariables->getVariableByName($variableName);
    }

    public function getVariableValueByName(string $variableName): int
    {
        return $this->daedalusVariables->getValueByName($variableName);
    }

    public function setVariableValueByName(int $value, string $variableName): static
    {
        $this->daedalusVariables->setValueByName($value, $variableName);

        return $this;
    }

    public function getGameVariables(): DaedalusVariables
    {
        return $this->daedalusVariables;
    }

    public function hasVariable(string $variableName): bool
    {
        return $this->daedalusVariables->hasVariable($variableName);
    }

    public function setDaedalusVariables(DaedalusConfig $daedalusConfig): static
    {
        $this->daedalusVariables = new DaedalusVariables($daedalusConfig);

        return $this;
    }

    public function getOxygen(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::OXYGEN);
    }

    public function setOxygen(int $oxygen): static
    {
        $this->setVariableValueByName($oxygen, DaedalusVariableEnum::OXYGEN);

        return $this;
    }

    public function getFuel(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::FUEL);
    }

    public function setFuel(int $fuel): static
    {
        $this->setVariableValueByName($fuel, DaedalusVariableEnum::FUEL);

        return $this;
    }

    public function getHull(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::HULL);
    }

    public function setHull(int $hull): static
    {
        $this->setVariableValueByName($hull, DaedalusVariableEnum::HULL);

        return $this;
    }

    public function getShield(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::SHIELD);
    }

    public function setShield(int $shield): static
    {
        $this->setVariableValueByName($shield, DaedalusVariableEnum::SHIELD);

        return $this;
    }

    public function getSpores(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::SPORE);
    }

    public function setSpores(int $spores): static
    {
        $this->setVariableValueByName($spores, DaedalusVariableEnum::SPORE);

        return $this;
    }

    public function getHunterPoints(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::HUNTER_POINTS);
    }

    public function setHunterPoints(int $hunterPoints): static
    {
        $this->setVariableValueByName($hunterPoints, DaedalusVariableEnum::HUNTER_POINTS);

        return $this;
    }

    public function addHunterPoints(int $hunterPoints): static
    {
        $this->setHunterPoints($this->getHunterPoints() + $hunterPoints);

        return $this;
    }

    public function removeHunterPoints(int $hunterPoints): static
    {
        $this->setHunterPoints($this->getHunterPoints() - $hunterPoints);

        return $this;
    }

    public function getCombustionChamberFuel(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL);
    }

    public function setCombustionChamberFuel(int $combustionChamberFuel): static
    {
        $this->setVariableValueByName($combustionChamberFuel, DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL);

        return $this;
    }

    public function addCombustionChamberFuel(int $combustionChamberFuel): static
    {
        $this->setCombustionChamberFuel($this->getCombustionChamberFuel() + $combustionChamberFuel);

        return $this;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->getStatuses()->contains($status)) {
            if (!$statusTarget = $status->getStatusTargetTarget()) {
                $statusTarget = new StatusTarget();
            }
            $statusTarget->setOwner($status);
            $statusTarget->setDaedalus($this);
            $this->statuses->add($statusTarget);
        }

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

    public function getFilledAt(): ?\DateTime
    {
        return $this->filledAt;
    }

    public function setFilledAt(\DateTime $filledAt): static
    {
        $this->filledAt = $filledAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTime $finishedAt): static
    {
        $this->finishedAt = $finishedAt;
        $this->daedalusInfo->getClosedDaedalus()->setFinishedAt($finishedAt);

        return $this;
    }

    public function getCycleStartedAt(): ?\DateTime
    {
        return $this->cycleStartedAt;
    }

    public function setCycleStartedAt(\DateTime $cycleStartedAt): static
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

    public function getDailyActionPointsSpent(): int
    {
        return $this->dailyActionPointsSpent;
    }

    public function setDailyActionSpent(int $dailyActionPointsSpent): static
    {
        $this->dailyActionPointsSpent = $dailyActionPointsSpent;

        return $this;
    }

    public function addDailyActionPointsSpent(int $dailyActionPointsSpent): static
    {
        $this->dailyActionPointsSpent += $dailyActionPointsSpent;

        return $this;
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function setOrientation(string $orientation): static
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getDestination(): SpaceCoordinates
    {
        return new SpaceCoordinates(
            orientation: $this->orientation,
            distance: $this->getCombustionChamberFuel(),
        );
    }

    public function getExploration(): ?Exploration
    {
        return $this->exploration;
    }

    public function setExploration(?Exploration $exploration): static
    {
        $this->exploration = $exploration;

        return $this;
    }

    public function getInOrbitPlanet(): ?Planet
    {
        return $this->exploration?->getPlanet();
    }

    public function getClassName(): string
    {
        return get_class($this);
    }

    public function getLanguage(): string
    {
        return $this->daedalusInfo->getLanguage();
    }

    public function getGameConfig(): GameConfig
    {
        return $this->daedalusInfo->getGameConfig();
    }

    public function getGameStatus(): string
    {
        return $this->daedalusInfo->getGameStatus();
    }

    public function getName(): string
    {
        return $this->daedalusInfo->getName();
    }

    public function getDaedalus(): self
    {
        return $this;
    }

    public function getDifficultyMode(): int
    {
        return $this->day;
    }

    public function isInHardMode(): bool
    {
        return !$this->isInVeryHardMode() && $this->day >= $this->daedalusInfo->getGameConfig()->getDifficultyConfig()->getDifficultyModes()->get(DifficultyEnum::HARD);
    }

    public function isInVeryHardMode(): bool
    {
        return $this->getDay() >= $this->daedalusInfo->getGameConfig()->getDifficultyConfig()->getDifficultyModes()->get(DifficultyEnum::VERY_HARD);
    }

    public function isInHunterSafeCycle(): bool
    {
        $safeCycles = new ArrayCollection($this->daedalusInfo->getGameConfig()->getDifficultyConfig()->getHunterSafeCycles());

        return $safeCycles->contains($this->cycle);
    }

    /** Implementation of `HunterTargetEntityInterface`. Always returns `false`. */
    public function isInAPatrolShip(): false
    {
        return false;
    }

    /** Implementation of `HunterTargetEntityInterface`. Always returns `false`. */
    public function isInSpace(): false
    {
        return false;
    }

    /** Implementation of `HunterTargetEntityInterface`. Always returns `true`. */
    public function isInSpaceBattle(): true
    {
        return true;
    }

    public function hasAnOngoingExploration(): bool
    {
        return $this->exploration !== null;
    }

    public function getLostPlayers(): PlayerCollection
    {
        return $this->getPlayers()->getPlayerAlive()->filter(fn (Player $player) => $player->hasStatus(PlayerStatusEnum::LOST));
    }
}
