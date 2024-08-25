<?php

namespace Mush\Player\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionHolderInterface;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Communication\Entity\Message;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Hunter\Entity\HunterTargetEntityInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierHolderTrait;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\PlayerRepository;
use Mush\Project\Entity\Project;
use Mush\Project\ValueObject\PlayerEfficiency;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Entity\SkillCollection;
use Mush\Skill\Entity\SkillConfigCollection;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player implements StatusHolderInterface, LogParameterInterface, ModifierHolderInterface, EquipmentHolderInterface, GameVariableHolderInterface, HunterTargetEntityInterface, ActionHolderInterface, ActionProviderInterface, ModifierProviderInterface
{
    use ModifierHolderTrait;
    use TargetStatusTrait;
    use TimestampableEntity;

    private const int MAX_ACTION_HISTORY = 20;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(mappedBy: 'player', targetEntity: PlayerInfo::class)]
    private PlayerInfo $playerInfo;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'players')]
    private Daedalus $daedalus;

    #[ORM\ManyToOne(targetEntity: Place::class, inversedBy: 'players')]
    private Place $place;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: GameItem::class)]
    private Collection $items;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: StatusTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: PlayerDisease::class)]
    private Collection $medicalConditions;

    #[ORM\ManyToMany(targetEntity: self::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'player_player_flirts')]
    private Collection $flirts;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: ModifierHolder::class, cascade: ['REMOVE'])]
    private Collection $modifiers;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: Skill::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $skills;

    #[ORM\OneToOne(targetEntity: GameVariableCollection::class, cascade: ['ALL'])]
    private PlayerVariables $playerVariables;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $triumph = 0;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: Planet::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $planets;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $titles = [];

    #[ORM\ManyToOne(targetEntity: Exploration::class, inversedBy: 'explorators')]
    private ?Exploration $exploration = null;

    #[ORM\ManyToMany(targetEntity: Message::class, mappedBy: 'favorites')]
    #[OrderBy(['updatedAt' => Order::Descending->value])]
    private Collection $favoriteMessages;

    #[ORM\Column(type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $lastActionDate;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $actionHistory = [];

    #[ORM\OneToOne(mappedBy: 'player', targetEntity: PlayerNotification::class)]
    private ?PlayerNotification $notification = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->statuses = new ArrayCollection();
        $this->medicalConditions = new PlayerDiseaseCollection();
        $this->flirts = new PlayerCollection();
        $this->modifiers = new ModifierCollection();
        $this->skills = new ArrayCollection();
        $this->planets = new ArrayCollection();
        $this->favoriteMessages = new ArrayCollection();
        $this->lastActionDate = new \DateTime();
    }

    public static function createNull(): self
    {
        return PlayerFactory::createNullPlayer();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayerInfo(): PlayerInfo
    {
        return $this->playerInfo;
    }

    public function setPlayerInfo(PlayerInfo $playerInfo): static
    {
        $this->playerInfo = $playerInfo;

        return $this;
    }

    public function getCharacterConfig(): CharacterConfig
    {
        return $this->playerInfo->getCharacterConfig();
    }

    public function getUser(): User
    {
        return $this->playerInfo->getUser();
    }

    public function getName(): string
    {
        return $this->playerInfo->getName();
    }

    public function isAlive(): bool
    {
        return $this->playerInfo->isAlive();
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): static
    {
        $this->daedalus = $daedalus;

        $daedalus->addPlayer($this);

        return $this;
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalus->getDaedalusInfo();
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function setPlace(Place $place): static
    {
        $this->place = $place;
        $place->addPlayer($this);

        return $this;
    }

    public function changePlace(Place $place): static
    {
        if ($this->place !== $place) {
            $this->place->removePlayer($this);
        }

        $this->place = $place;
        $place->addPlayer($this);

        return $this;
    }

    /**
     * This method returns all rooms connected to player's one by a working door.
     *
     * @return Collection<array-key, Place>
     */
    public function getAccessibleRooms(): Collection
    {
        return $this->getPlace()->getAccessibleRooms();
    }

    /**
     * Return true if the item is reachable for the player i.e. in the inventory or the room.
     */
    public function canReachEquipment(GameEquipment $gameEquipment): bool
    {
        if ($gameEquipment instanceof Door && $gameEquipment->getRooms()->contains($this->getPlace())) {
            return true;
        }

        if ($gameEquipment->getOwner() !== $this && $gameEquipment->getEquipment()->isPersonal()) {
            return false;
        }

        if ($hiddenStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN)) {
            return $hiddenStatus->getTarget() === $this;
        }

        return $this->items->contains($gameEquipment) || $this->getPlace()->getEquipments()->contains($gameEquipment);
    }

    public function cannotReachEquipment(GameEquipment $gameEquipment): bool
    {
        return $this->canReachEquipment($gameEquipment) === false;
    }

    public function getEquipments(): Collection
    {
        return $this->items;
    }

    public function setEquipments(ArrayCollection $equipments): static
    {
        $this->items = $equipments;

        return $this;
    }

    public function addEquipment(GameEquipment $gameEquipment): static
    {
        if (!$gameEquipment instanceof GameItem) {
            throw new UnexpectedTypeException($gameEquipment, GameItem::class);
        }
        if (!$this->getEquipments()->contains($gameEquipment)) {
            $this->getEquipments()->add($gameEquipment);
            $gameEquipment->setHolder($this);
        }

        return $this;
    }

    public function removeEquipment(GameEquipment $gameEquipment): static
    {
        if ($this->items->contains($gameEquipment)) {
            $this->items->removeElement($gameEquipment);
        }

        return $this;
    }

    public function hasEquipmentByName(string $name): bool
    {
        return !$this->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->getName() === $name)->isEmpty();
    }

    public function hasOperationalEquipmentByName(string $name): bool
    {
        return !$this->getEquipments()->filter(
            static fn (GameItem $gameItem) => $gameItem->getName() === $name && $gameItem->isOperational()
        )->isEmpty();
    }

    public function getEquipmentByName(string $name): ?GameEquipment
    {
        $equipment = $this->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->getName() === $name);

        return $equipment->isEmpty() ? null : $equipment->first();
    }

    public function addStatus(Status $status): static
    {
        if (!$this->getStatuses()->contains($status)) {
            if (!$statusTarget = $status->getStatusTargetTarget()) {
                $statusTarget = new StatusTarget();
            }

            $statusTarget->setOwner($status);
            $statusTarget->setPlayer($this);
            $this->statuses->add($statusTarget);
        }

        return $this;
    }

    public function isMush(): bool
    {
        return $this->hasStatus(PlayerStatusEnum::MUSH);
    }

    public function getMedicalConditions(): PlayerDiseaseCollection
    {
        if (!$this->medicalConditions instanceof PlayerDiseaseCollection) {
            $this->medicalConditions = new PlayerDiseaseCollection($this->medicalConditions->toArray());
        }

        return $this->medicalConditions;
    }

    public function getMedicalConditionByName(string $diseaseName): ?PlayerDisease
    {
        $disease = $this->medicalConditions->filter(static fn (PlayerDisease $playerDisease) => ($playerDisease->getDiseaseConfig()->getDiseaseName() === $diseaseName));

        return $disease->isEmpty() ? null : $disease->first();
    }

    public function getPhysicalDiseases(): PlayerDiseaseCollection
    {
        return $this->getMedicalConditions()->filter(static fn (PlayerDisease $playerDisease) => $playerDisease->isAPhysicalDisease());
    }

    public function getDisorders(): PlayerDiseaseCollection
    {
        return $this->getMedicalConditions()->filter(static fn (PlayerDisease $playerDisease) => $playerDisease->isADisorder());
    }

    public function getDisorderWithMostDiseasePoints(): PlayerDisease
    {
        return $this->getDisorders()->getSortedByDiseasePoints(order: Order::Descending)->first() ?: PlayerDisease::createNull();
    }

    public function setMedicalConditions(Collection $medicalConditions): static
    {
        $this->medicalConditions = $medicalConditions;

        return $this;
    }

    public function addMedicalCondition(PlayerDisease $playerDisease): static
    {
        $this->medicalConditions->add($playerDisease);

        return $this;
    }

    public function getAllModifiers(): ModifierCollection
    {
        $allModifiers = $this->getModifiers();
        $allModifiers = $allModifiers->addModifiers($this->place->getModifiers());

        return $allModifiers->addModifiers($this->daedalus->getModifiers());
    }

    public function getAllModifierConfigs(): ArrayCollection
    {
        $modifierConfigs = [];

        // then modifiers provided by player statuses
        $modifierConfigs = $this->getStatuses()
            ->map(static fn (Status $status) => $status->getStatusConfig()->getModifierConfigs())
            ->reduce(static fn (array $modifierConfigs, $statusModifierConfigs) => array_merge($modifierConfigs, $statusModifierConfigs->toArray()), $modifierConfigs);

        // then modifiers provided by player skills
        $modifierConfigs = $this->getSkills()
            ->map(static fn (Skill $skill) => $skill->getConfig()->getModifierConfigs())
            ->reduce(static fn (array $modifierConfigs, $skillModifierConfigs) => array_merge($modifierConfigs, $skillModifierConfigs->toArray()), $modifierConfigs);

        // then modifiers provided by disease
        $modifierConfigs = $this->getMedicalConditions()
            ->map(static fn (PlayerDisease $playerDisease) => $playerDisease->getDiseaseConfig()->getModifierConfigs())
            ->reduce(static fn (array $modifierConfigs, $skillModifierConfigs) => array_merge($modifierConfigs, $skillModifierConfigs->toArray()), $modifierConfigs);

        return new ArrayCollection($modifierConfigs);
    }

    public function getFlirts(): PlayerCollection
    {
        if (!$this->flirts instanceof PlayerCollection) {
            $this->flirts = new PlayerCollection($this->flirts->toArray());
        }

        return $this->flirts;
    }

    public function setFlirts(Collection $flirts): static
    {
        $this->flirts = $flirts;

        return $this;
    }

    public function addFlirt(self $playerFlirt): static
    {
        $this->flirts->add($playerFlirt);

        return $this;
    }

    public function HasFlirtedWith(self $playerTarget): bool
    {
        return $this->getFlirts()->exists(static fn (int $id, Player $player) => $player === $playerTarget);
    }

    public function addSkill(Skill $skill): static
    {
        $this->skills->add($skill);

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        $this->skills->removeElement($skill);

        return $this;
    }

    public function getSkills(): SkillCollection
    {
        return new SkillCollection($this->skills->toArray());
    }

    public function getHumanSkills(): SkillCollection
    {
        return $this->getSkills()->getHumanSkills();
    }

    public function getMushSkills(): SkillCollection
    {
        return $this->getSkills()->getMushSkills();
    }

    public function getSkillsWithPoints(): SkillCollection
    {
        return $this->getSkills()->getSkillsWithPoints();
    }

    public function getSkillByNameOrThrow(SkillEnum $name): Skill
    {
        $skill = $this->getSkillByNameOrNull($name);

        return $skill ?? throw new \Exception('The player does not have the skill ' . $name->value);
    }

    public function hasSkill(SkillEnum $skillName): bool
    {
        return $this->getSkills()->exists(static fn ($_, Skill $skill) => $skill->getName() === $skillName);
    }

    public function getSelectableHumanSkills(): SkillConfigCollection
    {
        if ($this->hasFilledTheirHumanSkillSlots()) {
            return new SkillConfigCollection();
        }

        $selectableSkills = $this->getHumanSkillConfigs()->getAllExceptThoseLearnedByPlayer($this);

        return $this->hasStatus(PlayerStatusEnum::HAS_LEARNED_SKILL) ? $selectableSkills->getAllExcept(SkillEnum::APPRENTICE) : $selectableSkills;
    }

    public function getSelectableMushSkills(): SkillConfigCollection
    {
        if ($this->hasFilledTheirMushSkillSlots()) {
            return new SkillConfigCollection();
        }

        return $this->isMush() ? $this->daedalus->getMushSkillConfigs()->getAllExceptThoseLearnedByPlayer($this) : new SkillConfigCollection();
    }

    public function cannotTakeSkill(SkillEnum $skill): bool
    {
        $selectableSkills = $skill->isMushSkill() ? $this->getSelectableMushSkills() : $this->getSelectableHumanSkills();

        return $selectableSkills->doesNotContain($skill);
    }

    public function getGameVariables(): PlayerVariables
    {
        return $this->playerVariables;
    }

    public function setPlayerVariables(CharacterConfig $characterConfig): static
    {
        $this->playerVariables = new PlayerVariables($characterConfig);

        return $this;
    }

    public function getHealthPoint(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::HEALTH_POINT);
    }

    public function setHealthPoint(int $healthPoint): static
    {
        $this->playerVariables->setValueByName($healthPoint, PlayerVariableEnum::HEALTH_POINT);

        return $this;
    }

    public function getMoralPoint(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::MORAL_POINT);
    }

    public function setMoralPoint(int $moralPoint): static
    {
        $this->playerVariables->setValueByName($moralPoint, PlayerVariableEnum::MORAL_POINT);

        return $this;
    }

    public function getActionPoint(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::ACTION_POINT);
    }

    public function setActionPoint(int $actionPoint): static
    {
        $this->playerVariables->setValueByName($actionPoint, PlayerVariableEnum::ACTION_POINT);

        return $this;
    }

    public function hasSpentActionPoints(): bool
    {
        return $this->getActionPoint() < $this->getCharacterConfig()->getMaxActionPoint();
    }

    public function getMovementPoint(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::MOVEMENT_POINT);
    }

    public function setMovementPoint(int $movementPoint): static
    {
        $this->playerVariables->setValueByName($movementPoint, PlayerVariableEnum::MOVEMENT_POINT);

        return $this;
    }

    public function getSatiety(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::SATIETY);
    }

    public function setSatiety(int $satiety): static
    {
        $this->playerVariables->setValueByName($satiety, PlayerVariableEnum::SATIETY);

        return $this;
    }

    public function getSpores(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::SPORE);
    }

    public function setSpores(int $spores): static
    {
        $this->playerVariables->setValueByName($spores, PlayerVariableEnum::SPORE);

        return $this;
    }

    public function getTriumph(): int
    {
        return $this->triumph;
    }

    public function setTriumph(int $triumph): static
    {
        $this->triumph = $triumph;

        return $this;
    }

    public function addTriumph(int $triumph): static
    {
        $this->triumph += $triumph;

        return $this;
    }

    public function getPlanets(): Collection
    {
        return $this->planets;
    }

    public function addPlanet(Planet $planet): static
    {
        $this->planets->add($planet);

        return $this;
    }

    public function removePlanet(Planet $planet): static
    {
        $this->planets->removeElement($planet);

        return $this;
    }

    public function getVariableByName(string $variableName): GameVariable
    {
        return $this->playerVariables->getVariableByName($variableName);
    }

    public function hasVariable(string $variableName): bool
    {
        return $this->playerVariables->hasVariable($variableName);
    }

    public function getVariableValueByName(string $variableName): int
    {
        return $this->playerVariables->getValueByName($variableName);
    }

    public function setVariableValueByName(string $variableName, int $value): static
    {
        $this->playerVariables->setValueByName($value, $variableName);

        return $this;
    }

    public function getClassName(): string
    {
        return static::class;
    }

    public function getLogName(): string
    {
        return $this->playerInfo->getName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::CHARACTER;
    }

    public function getPlayer(): self
    {
        return $this;
    }

    public function getGameEquipment(): ?GameEquipment
    {
        return null;
    }

    public function canSeeSpaceBattle(): bool
    {
        $spaceBattleRooms = array_merge(
            RoomEnum::getPatrolships()->toArray(),
            RoomEnum::getTurrets()->toArray(),
        );

        return \in_array($this->getPlace()->getName(), $spaceBattleRooms, true);
    }

    public function isInAPatrolShip(): bool
    {
        return $this->getPlace()->getType() === PlaceTypeEnum::PATROL_SHIP;
    }

    public function isInSpace(): bool
    {
        return $this->getPlace()->getType() === PlaceTypeEnum::SPACE;
    }

    public function isInSpaceBattle(): bool
    {
        return $this->isInAPatrolShip() || $this->isInSpace();
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function getFocusedTerminal(): ?GameEquipment
    {
        return $this->getStatusByName(PlayerStatusEnum::FOCUSED)?->getTarget();
    }

    public function addTitle(string $title): static
    {
        $this->titles[] = $title;

        return $this;
    }

    public function removeTitle(string $title): static
    {
        if (\in_array($title, $this->titles, true)) {
            $this->titles = array_diff($this->titles, [$title]);
        }

        return $this;
    }

    public function getTitles(): array
    {
        return $this->titles;
    }

    public function setTitles(array $titles): static
    {
        $this->titles = $titles;

        return $this;
    }

    public function hasTitle(string $title): bool
    {
        return \in_array($title, $this->getTitles(), true);
    }

    public function setExploration(?Exploration $exploration): static
    {
        $this->exploration = $exploration;

        return $this;
    }

    public function isExploring(): bool
    {
        return $this->exploration !== null;
    }

    public function isExploringOrIsLostOnPlanet(): bool
    {
        return $this->exploration !== null || $this->hasStatus(PlayerStatusEnum::LOST);
    }

    public function getFavoriteMessages(): Collection
    {
        return $this->favoriteMessages;
    }

    public function isFocusedOnTerminalByName(string $terminalName): bool
    {
        return $this->getFocusedTerminal()?->getName() === $terminalName;
    }

    public function getEfficiencyForProject(Project $project): PlayerEfficiency
    {
        $max = $this->getMaxEfficiencyForProject($project);
        $min = $this->daedalus->hasAliveNeronOnlyFriend() && $project->isNeronProject() ? $max : $this->getMinEfficiencyForProject($project);

        return new PlayerEfficiency($min, $max);
    }

    public function efficiencyIsZeroForProject(Project $project): bool
    {
        return $this->getEfficiencyForProject($project)->max === 0;
    }

    public function getOperationalStatus(string $actionName): ActionProviderOperationalStateEnum
    {
        $charge = $this->getUsedCharge($actionName);
        if ($charge !== null && !$charge->isCharged()) {
            return ActionProviderOperationalStateEnum::DISCHARGED;
        }

        return ActionProviderOperationalStateEnum::OPERATIONAL;
    }

    public function getUsedCharge(string $actionName): ?ChargeStatus
    {
        $charges = $this->getStatuses()->filter(static fn (Status $status) => $status instanceof ChargeStatus && $status->hasDischargeStrategy($actionName));

        $charge = $charges->first();
        if (!$charge instanceof ChargeStatus) {
            return null;
        }

        return $charge;
    }

    // return action available for this target $actionTarget can either be set to player or target_player
    public function getActions(self $activePlayer, ?ActionHolderEnum $actionTarget = null): Collection
    {
        if ($actionTarget === null) {
            throw new \Exception('You must specify if the action holder is the current player or another player');
        }
        // first actions provided by the player entity
        $actions = $this->getProvidedActions($actionTarget, [ActionRangeEnum::SELF])->toArray();

        // then actions provided by the room
        $actions = array_merge($actions, $this->getProvidedActions($actionTarget, [ActionRangeEnum::ROOM, ActionRangeEnum::SHELF])->toArray());

        // then actions provided by the current player
        $actions = array_merge($actions, $activePlayer->getProvidedActions($actionTarget, [ActionRangeEnum::PLAYER, ActionRangeEnum::SHELF])->toArray());

        return new ArrayCollection($actions);
    }

    // return actions provided by this entity and the other actionProviders it bears
    public function getProvidedActions(ActionHolderEnum $actionTarget, array $actionRanges): Collection
    {
        $actions = [];

        // first actions given by the character config
        /** @var ActionConfig $actionConfig */
        foreach ($this->getCharacterConfig()->getActionConfigs() as $actionConfig) {
            if (
                $actionConfig->getDisplayHolder() === $actionTarget
                && \in_array($actionConfig->getRange(), $actionRanges, true)
            ) {
                $action = new Action();
                $action->setActionProvider($this)->setActionConfig($actionConfig);
                $actions[] = $action;
            }
        }

        // then actions provided by the statuses
        /** @var Status $status */
        foreach ($this->getStatuses() as $status) {
            $actions = array_merge($actions, $status->getProvidedActions($actionTarget, $actionRanges)->toArray());
        }

        // then actions provided by the inventory
        /** @var GameItem $equipment */
        foreach ($this->getEquipments() as $equipment) {
            $actions = array_merge($actions, $equipment->getProvidedActions($actionTarget, $actionRanges)->toArray());
        }

        // then actions provided by skills
        /** @var Skill $skill */
        foreach ($this->getSkills() as $skill) {
            $actions = array_merge($actions, $skill->getProvidedActions($actionTarget, $actionRanges)->toArray());
        }

        return new ArrayCollection($actions);
    }

    public function canPlayerReach(self $player): bool
    {
        return $this->getPlace() === $player->getPlace();
    }

    public function kill(): static
    {
        $this->playerInfo->setGameStatus(GameStatusEnum::FINISHED);

        return $this;
    }

    public function isNull(): bool
    {
        return $this->getName() === CharacterEnum::null;
    }

    public function isDead(): bool
    {
        return $this->isAlive() === false;
    }

    public function getAlivePlayersInRoomExceptSelf(): PlayerCollection
    {
        return $this->getPlace()->getPlayers()->getPlayerAlive()->filter(
            fn (Player $player) => $player->getId() !== $this->getId()
        );
    }

    public function isAloneInRoom(): bool
    {
        return $this->getPlace()->getNumberOfPlayersAlive() === 1;
    }

    public function isHuman(): bool
    {
        return $this->isMush() === false;
    }

    public function getLanguage(): string
    {
        return $this->getDaedalus()->getLanguage();
    }

    public function getWhosWhoColor(): string
    {
        return CharacterEnum::$characterColorMap[$this->getName()];
    }

    public function isLaidDownInShrinkRoom(): bool
    {
        return $this->hasStatus(PlayerStatusEnum::LYING_DOWN) && $this->place->hasAnAliveShrinkExceptPlayer($this);
    }

    public function lastActionIsFromYesterdayOrLater(): bool
    {
        return $this->lastActionDate <= new \DateTime('yesterday');
    }

    public function lastActionIsFromTwoDaysAgoOrLater(): bool
    {
        return $this->lastActionDate <= new \DateTime('-2 days');
    }

    public function updateLastActionDate(): static
    {
        $this->lastActionDate = new \DateTime();

        return $this;
    }

    /**
     * @return array<ActionEnum>
     */
    public function getActionHistory(int $limit = PHP_INT_MAX): array
    {
        return \array_slice($this->actionHistory, offset: 0, length: $limit);
    }

    public function addActionToHistory(ActionEnum $action): static
    {
        // should not add specific actions
        if ($action->shouldNotBeRecordedInHistory()) {
            return $this;
        }

        // Add the action to the beginning of the array
        array_unshift($this->actionHistory, $action);

        // Keep only a decent amount of actions in the history
        $this->actionHistory = \array_slice($this->actionHistory, offset: 0, length: self::MAX_ACTION_HISTORY);

        return $this;
    }

    public function hasNotification(): bool
    {
        return $this->notification !== null;
    }

    public function getNotificationOrThrow(): PlayerNotification
    {
        return $this->notification ?? throw new \RuntimeException('The player does not have a notification');
    }

    public function updateNotification(PlayerNotification $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    public function deleteNotification(): self
    {
        $this->notification = null;

        return $this;
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function isActive(): bool
    {
        return !$this->hasAnyStatuses(new ArrayCollection([PlayerStatusEnum::INACTIVE, PlayerStatusEnum::HIGHLY_INACTIVE]));
    }

    public function getHumanLevel(): int
    {
        return $this->getCharacterConfig()->getSkillConfigs()->count();
    }

    public function getMushLevel(): int
    {
        return $this->isMush() ? $this->daedalus->getMushSkillConfigs()->count() : 0;
    }

    public function canReadFoodProperties(GameEquipment $food): bool
    {
        return $this->canReadRationProperties($food) || $this->canReadFruitProperties($food) || $this->canReadDrugProperties($food);
    }

    public function canReadPlantProperties(GameEquipment $plant): bool
    {
        return match ($plant->isAPlant()) {
            true => $this->hasSkill(SkillEnum::BOTANIST),
            default => false,
        };
    }

    public function shouldBeHurtByShower(): bool
    {
        return $this->isMush() && $this->doesNotHaveSkill(SkillEnum::SPLASHPROOF);
    }

    public function hasFilledTheirHumanSkillSlots(): bool
    {
        return $this->getHumanSkills()->count() === $this->daedalus->getDaedalusConfig()->getHumanSkillSlots();
    }

    public function hasFilledTheirMushSkillSlots(): bool
    {
        return $this->getMushSkills()->count() === $this->daedalus->getDaedalusConfig()->getMushSkillSlots();
    }

    private function getMinEfficiencyForProject(Project $project): int
    {
        if ($this->hasStatus(PlayerStatusEnum::GENIUS_IDEA) && $project->isNotPilgred()) {
            return 100;
        }

        $efficiency = $this->getEfficiencyWithBonusSkills($project->getEfficiency(), $project);
        $efficiency = $this->getEfficiencyWithParticipationMalus($efficiency, $project);

        return $this->getEfficiencyWithCpuPriorityBonus($efficiency, $project);
    }

    private function getMaxEfficiencyForProject(Project $project): int
    {
        $efficiency = (int) ($this->getMinEfficiencyForProject($project) + $this->getMinEfficiencyForProject($project) / 2);

        return min($efficiency, 100);
    }

    private function getEfficiencyWithBonusSkills(int $efficiency, Project $project): int
    {
        $playerSkills = $this->getSkills()->map(static fn (Skill $skill) => $skill->getName()->toString())->toArray();
        $bonusSkills = array_map(static fn (SkillEnum $skill) => $skill->toString(), $project->getBonusSkills());
        $numberOfSkillsMatching = \count(array_intersect($playerSkills, $bonusSkills));

        return $efficiency + $numberOfSkillsMatching * Project::SKILL_BONUS;
    }

    private function getEfficiencyWithParticipationMalus(int $efficiency, Project $project): int
    {
        $efficiency -= $project->getPlayerParticipations($this) * Project::PARTICIPATION_MALUS;

        return max(0, $efficiency);
    }

    private function getEfficiencyWithCpuPriorityBonus(int $efficiency, Project $project): int
    {
        if ($this->daedalus->isCpuPriorityOn(NeronCpuPriorityEnum::PROJECTS) && $project->isNeronProject()) {
            return $efficiency + Project::CPU_PRIORITY_BONUS;
        }

        return $efficiency;
    }

    private function doesNotHaveSkill(SkillEnum $skillName): bool
    {
        return $this->hasSkill($skillName) === false;
    }

    private function getHumanSkillConfigs(): SkillConfigCollection
    {
        return $this->getCharacterConfig()->getSkillConfigs();
    }

    private function getSkillByNameOrNull(SkillEnum $name): ?Skill
    {
        $skill = $this->getSkills()->filter(static fn (Skill $skill) => $skill->getName() === $name)->first();

        return $skill ?: null;
    }

    private function canReadRationProperties(GameEquipment $food): bool
    {
        return $food->isARation() && $this->hasStatus(PlayerStatusEnum::MUSH);
    }

    private function canReadFruitProperties(GameEquipment $food): bool
    {
        return $food->isAFruit() && $this->hasSkill(SkillEnum::BOTANIST);
    }

    private function canReadDrugProperties(GameEquipment $food): bool
    {
        return $food->isADrug() && $this->hasSkill(SkillEnum::NURSE);
    }
}
