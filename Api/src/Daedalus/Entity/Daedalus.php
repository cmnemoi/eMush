<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Exploration\Entity\Exploration;
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
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierHolderTrait;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Project\Collection\ProjectCollection;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Exception\DaedalusShouldHaveProjectException;
use Mush\Skill\Entity\SkillConfigCollection;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

#[ORM\Entity(repositoryClass: DaedalusRepository::class)]
#[ORM\Table(name: 'daedalus')]
class Daedalus implements ModifierHolderInterface, GameVariableHolderInterface, HunterTargetEntityInterface, StatusHolderInterface
{
    use ModifierHolderTrait;
    use TargetStatusTrait;
    use TimestampableEntity;

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

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: ModifierHolder::class, cascade: ['REMOVE'])]
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

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: Project::class, orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $projects;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->places = new ArrayCollection();
        $this->modifiers = new ModifierCollection();
        $this->statuses = new ArrayCollection();
        $this->projects = new ArrayCollection();
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

    public function getActivePlayers(): PlayerCollection
    {
        return $this->getPlayers()->getActivePlayers();
    }

    public function removePlayer(Player $player): static
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getAlivePlayers(): PlayerCollection
    {
        return $this->getPlayers()->getPlayerAlive();
    }

    public function getPlayerByName(string $name): ?Player
    {
        return $this->getPlayers()->getPlayerByName($name);
    }

    public function getAlivePlayerByNameOrThrow(string $name): Player
    {
        $player = $this->getAlivePlayers()->getPlayerByName($name);
        if (!$player) {
            throw new \RuntimeException("Daedalus should have an alive player named {$name}");
        }

        return $player;
    }

    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function getRooms(): Collection
    {
        return $this->getPlaces()->filter(static fn (Place $place) => $place->getType() === PlaceTypeEnum::ROOM);
    }

    public function getSpace(): Place
    {
        $space = $this->getPlaces()->filter(static fn (Place $place) => $place->getName() === RoomEnum::SPACE)->first();
        if (!$space) {
            throw new \RuntimeException('Daedalus should have a place named Space');
        }

        return $space;
    }

    /**
     * @throws \RuntimeException if no planet place have been found
     */
    public function getPlanetPlace(): Place
    {
        $planetPlace = $this->getPlaces()->filter(static fn (Place $place) => $place->getName() === RoomEnum::PLANET)->first();
        if (!$planetPlace) {
            throw new \RuntimeException('Daedalus should have a planet place');
        }

        return $planetPlace;
    }

    public function getPlaceByName(string $name): ?Place
    {
        $place = $this->getPlaces()->filter(static fn (Place $place) => $place->getName() === $name)->first();

        return $place === false ? null : $place;
    }

    public function getPlaceByNameOrThrow(string $name): Place
    {
        $place = $this->getPlaceByName($name);
        if (!$place) {
            throw new \RuntimeException("Daedalus should have a place named {$name}");
        }

        return $place;
    }

    public function getRoomsOnFire(): Collection
    {
        return $this->getRooms()->filter(static fn (Place $place) => $place->hasStatus(StatusEnum::FIRE));
    }

    public function getRoomsWithoutFire(): Collection
    {
        return $this->getRooms()->filter(static fn (Place $place) => !$place->hasStatus(StatusEnum::FIRE));
    }

    public function getRoomsWithAlivePlayers(): Collection
    {
        return $this->getRooms()->filter(static fn (Place $place) => $place->getPlayers()->getPlayerAlive()->count() > 0);
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

    public function getAllModifiers(): ModifierCollection
    {
        return $this->getModifiers();
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

    public function isDaedalusOrExplorationChangingCycle(): bool
    {
        return $this->isCycleChange() || $this->isExplorationChangingCycle();
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

    public function getClassName(): string
    {
        return static::class;
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
        return $this->getPlayers()->getPlayerAlive()->filter(static fn (Player $player) => $player->hasStatus(PlayerStatusEnum::LOST));
    }

    public function getAllAvailableProjects(): Collection
    {
        return $this->projects->filter(static fn (Project $project) => $project->isAvailable());
    }

    public function getAvailableNeronProjects(): Collection
    {
        return $this->projects->filter(static fn (Project $project) => $project->isAvailableNeronProject());
    }

    public function getProposedNeronProjects(): ProjectCollection
    {
        return new ProjectCollection($this->projects->filter(static fn (Project $project) => $project->isProposedNeronProject())->toArray());
    }

    public function hasProposedNeronProjects(): bool
    {
        return $this->getProposedNeronProjects()->count() > 0;
    }

    public function getFinishedNeronProjects(): Collection
    {
        return $this->projects->filter(static fn (Project $project) => $project->isFinishedNeronProject());
    }

    public function getAdvancedNeronProjects(): ProjectCollection
    {
        return (new ProjectCollection($this->projects->toArray()))->getAdvancedNeronProjects();
    }

    public function addProject(Project $project): static
    {
        if ($this->projects->contains($project)) {
            return $this;
        }

        $this->projects->add($project);

        return $this;
    }

    public function getProjectByName(ProjectName $projectName): Project
    {
        $project = $this->projects->filter(static fn (Project $project) => $project->getName() === $projectName->value)->first();
        if (!$project) {
            throw new DaedalusShouldHaveProjectException($projectName);
        }

        return $project;
    }

    public function hasProject(ProjectName $projectName): bool
    {
        return $this->projects->exists(static fn ($key, Project $project) => $project->getName() === $projectName->value);
    }

    public function hasFinishedProject(ProjectName $projectName): bool
    {
        return $this->getProjectByName($projectName)->isFinished();
    }

    public function projectIsNotFinished(ProjectName $projectName): bool
    {
        return !$this->hasFinishedProject($projectName);
    }

    public function hasActiveProject(ProjectName $projectName): bool
    {
        return match ($projectName) {
            ProjectName::PLASMA_SHIELD => $this->isPlasmaShieldActive(),
            ProjectName::MAGNETIC_NET => $this->isMagneticNetActive(),
            default => $this->hasFinishedProject($projectName),
        };
    }

    public function isPlasmaShieldActive(): bool
    {
        return $this->hasFinishedProject(ProjectName::PLASMA_SHIELD) && $this->getNeron()->isPlasmaShieldActive();
    }

    public function isMagneticNetActive(): bool
    {
        return $this->hasFinishedProject(ProjectName::MAGNETIC_NET) && $this->getNeron()->isMagneticNetActive();
    }

    public function hasNoProposedNeronProjects(): bool
    {
        return $this->getProposedNeronProjects()->isEmpty();
    }

    public function getPilgred(): Project
    {
        return $this->getProjectByName(ProjectName::PILGRED);
    }

    public function isPilgredFinished(): bool
    {
        return $this->getPilgred()->isFinished();
    }

    public function pilgredIsNotFinished(): bool
    {
        return $this->isPilgredFinished() === false;
    }

    public function getDaedalusConfig(): DaedalusConfig
    {
        return $this->getGameConfig()->getDaedalusConfig();
    }

    public function getProjectConfigs(): Collection
    {
        return $this->getGameConfig()->getProjectConfigs();
    }

    public function isCpuPriorityOn(string $cpuPriority): bool
    {
        return $this->getDaedalusInfo()->getNeron()->getCpuPriority() === $cpuPriority;
    }

    public function getNumberOfProjectsByBatch(): int
    {
        return $this->getDaedalusConfig()->getNumberOfProjectsByBatch();
    }

    public function getAlivePlayersInSpaceBattle(): Collection
    {
        return $this->getPlayers()->getPlayerAlive()->filter(static fn (Player $player) => $player->isInSpaceBattle());
    }

    public function getNumberOfFiresKilledByAutoWateringProject(): int
    {
        return $this->getChargeStatusByName(DaedalusStatusEnum::AUTO_WATERING_KILLED_FIRES)?->getCharge() ?? 0;
    }

    public function fissionCoffeeRoasterNotReady(): bool
    {
        return $this->hasFinishedProject(ProjectName::FISSION_COFFEE_ROASTER) === false || $this->cycle !== 4;
    }

    public function doesNotHaveAutoReturnIcarusProject(): bool
    {
        return $this->hasFinishedProject(ProjectName::AUTO_RETURN_ICARUS) === false;
    }

    public function getNeron(): Neron
    {
        return $this->daedalusInfo->getNeron();
    }

    public function getNumberOfCyclesPerDay(): int
    {
        return $this->getDaedalusConfig()->getCyclePerGameDay();
    }

    public function getMushSkillConfigs(): SkillConfigCollection
    {
        return new SkillConfigCollection($this->getGameConfig()->getMushSkillConfigs()->toArray());
    }

    public function hasAliveNeronOnlyFriend(): bool
    {
        return $this->getAlivePlayers()->hasPlayerWithSkill(SkillEnum::NERON_ONLY_FRIEND);
    }

    public function getAlivePlayersWithMeansOfCommunication(): PlayerCollection
    {
        return $this->getAlivePlayers()->filter(static fn (Player $player) => $player->hasMeansOfCommunication());
    }

    public function getDaysElapsedSinceCreation(): int
    {
        return $this->getCreatedAtOrThrow()->diff(new \DateTime('now'))->days;
    }

    public function getPlace(): Place
    {
        throw new \RuntimeException('Daedalus does not implement getPlace method');
    }

    private function isExplorationChangingCycle(): bool
    {
        return $this->getExploration()?->isChangingCycle() ?? false;
    }

    private function getCreatedAtOrThrow(): \DateTime
    {
        $createdAt = $this->getCreatedAt();
        if (!$createdAt) {
            throw new \RuntimeException('Daedalus should have a creation date');
        }

        return $createdAt;
    }
}
