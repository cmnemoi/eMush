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
use Mush\Chat\Entity\Message;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Hunter\Entity\HunterTargetEntityInterface;
use Mush\MetaGame\Entity\Skin\SkinableEntityInterface;
use Mush\MetaGame\Entity\Skin\SkinableEntityTrait;
use Mush\MetaGame\Entity\Skin\SkinSlot;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierHolderTrait;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\PlayerRepository;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Player\ValueObject\PlayerHighlightTargetInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\ValueObject\PlayerEfficiency;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Entity\SkillCollection;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Entity\SkillConfigCollection;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Entity\VisibleStatusHolderInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player implements StatusHolderInterface, VisibleStatusHolderInterface, LogParameterInterface, ModifierHolderInterface, EquipmentHolderInterface, GameVariableHolderInterface, HunterTargetEntityInterface, ActionHolderInterface, ActionProviderInterface, ModifierProviderInterface, PlayerHighlightTargetInterface, SkinableEntityInterface
{
    use ModifierHolderTrait;
    use SkinableEntityTrait;
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

    #[ORM\ManyToMany(targetEntity: SkillConfig::class)]
    private Collection $availableSkills;

    #[ORM\OneToOne(targetEntity: GameVariableCollection::class, cascade: ['ALL'])]
    private PlayerVariables $playerVariables;

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

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: PlayerNotification::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'subordinate', targetEntity: CommanderMission::class, orphanRemoval: true)]
    #[OrderBy(['createdAt' => Order::Descending->value])]
    private Collection $receivedMissions;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: SkinSlot::class, cascade: ['ALL'])]
    private Collection $skinSlots;

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
        $this->notifications = new ArrayCollection();
        $this->receivedMissions = new ArrayCollection();
        $this->skinSlots = new ArrayCollection();
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

    public function isIn(string $placeName): bool
    {
        return $this->getPlace()->getName() === $placeName;
    }

    public function isInAny(array $placeNames): bool
    {
        return \in_array($this->getPlace()->getName(), $placeNames, true);
    }

    public function isNotIn(string $placeName): bool
    {
        return $this->getPlace()->getName() !== $placeName;
    }

    public function isNotInAny(array $placeNames): bool
    {
        return \in_array($this->getPlace()->getName(), $placeNames, true) === false;
    }

    public function getPreviousRoom(): ?Place
    {
        return $this->getStatusByName(PlayerStatusEnum::PREVIOUS_ROOM)?->getPlaceTargetOrThrow();
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

    public function canReachEquipmentByName(string $gameEquipmentName): bool
    {
        return $this->hasEquipmentByName($gameEquipmentName) || $this->getPlace()->hasEquipmentByName($gameEquipmentName);
    }

    public function canReachFood(): bool
    {
        $playerFood = $this->items->filter(static fn (GameItem $item) => $item->isARation());
        $placeFood = $this->getPlace()->getEquipments()->filter(static fn (GameEquipment $equipment) => $equipment->isARation());

        return $playerFood->count() > 0 || $placeFood->count() > 0;
    }

    public function hasSampleAvailable(): bool
    {
        return $this->getDaedalus()->getStatusByName(DaedalusStatusEnum::GHOST_SAMPLE)
        || $this->canReachEquipmentByName(ItemEnum::MUSH_SAMPLE);
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
        return $this->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->getName() === $name)->isEmpty() === false;
    }

    public function hasOperationalEquipmentByName(string $name): bool
    {
        return $this->getEquipments()->filter(
            static fn (GameItem $gameItem) => $gameItem->getName() === $name && $gameItem->isOperational()
        )->isEmpty() === false;
    }

    public function doesNotHaveEquipment(string $name): bool
    {
        return $this->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->getName() === $name)->isEmpty();
    }

    public function hasAnyOperationalEquipment(array $names): bool
    {
        $operationalEquipments = $this->getEquipments()
            ->filter(static fn (GameItem $gameItem) => $gameItem->isOperational())
            ->map(static fn (GameItem $gameItem) => $gameItem->getName())
            ->toArray();

        $matchingEquipments = array_intersect($names, $operationalEquipments);

        return empty($matchingEquipments) === false;
    }

    public function doesNotHaveAnyOperationalEquipment(array $names): bool
    {
        return $this->hasAnyOperationalEquipment($names) === false;
    }

    public function getEquipmentByName(string $name): ?GameEquipment
    {
        $equipment = $this->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->getName() === $name);

        return $equipment->isEmpty() ? null : $equipment->first();
    }

    public function getEquipmentByNameOrThrow(string $name): GameEquipment
    {
        $equipment = $this->getEquipmentByName($name);

        return $equipment ?? throw new \Exception('The player does not have the equipment ' . $name);
    }

    /**
     * @return Collection<array-key, GameItem>
     */
    public function getCriticalItemsForMe(): Collection
    {
        return $this->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->isCritical());
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
        return $this->isAlive() ? $this->hasStatus(PlayerStatusEnum::MUSH) : $this->playerInfo->getClosedPlayer()->isMush();
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

    public function getMedicalConditionByNameOrThrow(string $diseaseName): PlayerDisease
    {
        $disease = $this->getMedicalConditionByName($diseaseName);

        return $disease ?? throw new \RuntimeException('The player does not have the disease ' . $diseaseName);
    }

    public function getActiveDisorders(): PlayerDiseaseCollection
    {
        return $this->getMedicalConditions()->getActiveDiseases()->getByDiseaseType(MedicalConditionTypeEnum::DISORDER);
    }

    public function getActiveDiseasesHealingAtCycleChange(): PlayerDiseaseCollection
    {
        return $this->getMedicalConditions()->getActiveDiseases()->filter(static fn (PlayerDisease $playerDisease) => $playerDisease->healsAtCycleChange());
    }

    public function hasActiveDisorder(): bool
    {
        return $this->getActiveDisorders()->count() > 0;
    }

    public function hasActiveDiseaseHealingAtCycleChange(): bool
    {
        return $this->getActiveDiseasesHealingAtCycleChange()->count() > 0;
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

    public function removeMedicalCondition(PlayerDisease $playerDisease): static
    {
        $this->medicalConditions->removeElement($playerDisease);

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

    public function hasFlirtedWith(self $playerTarget): bool
    {
        return $this->flirts->contains($playerTarget);
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

    public function getMushAndHumanSkills(): SkillCollection
    {
        $mushSkills = $this->getMushSkills()->getSortedBy('createdAt');
        $humanSkills = $this->getHumanSkills()->getSortedBy('createdAt');

        return $mushSkills->addSkills($humanSkills);
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
        return $this->hasStandaloneSkill($skillName) || $this->hasSkillThroughPolyvalent($skillName);
    }

    /** @param array<SkillEnum> $expectedSkills */
    public function hasAnySkill(array $expectedSkills): bool
    {
        foreach ($expectedSkills as $skill) {
            if ($this->hasSkill($skill)) {
                return true;
            }
        }

        return false;
    }

    public function doesNotHaveSkill(SkillEnum $skillName): bool
    {
        return $this->hasSkill($skillName) === false;
    }

    public function setAvailableHumanSkills(SkillConfigCollection $skillsConfig): static
    {
        $this->availableSkills = $skillsConfig;

        return $this;
    }

    public function removeFromAvailableHumanSkills(SkillConfig $skill): static
    {
        $this->availableSkills->removeElement($skill);

        return $this;
    }

    public function addToAvailableHumanSkills(SkillConfig $skill): static
    {
        $this->availableSkills->add($skill);

        return $this;
    }

    public function getAvailableHumanSkills(): SkillConfigCollection
    {
        return new SkillConfigCollection($this->availableSkills->toArray());
    }

    public function getSelectableHumanSkills(): SkillConfigCollection
    {
        if ($this->hasFilledTheirHumanSkillSlots()) {
            return new SkillConfigCollection();
        }

        return $this->getAvailableHumanSkills()->getAllExceptThoseLearnedByPlayer($this);
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

    public function isFullHealth(): bool
    {
        return $this->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->isMax();
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

    public function hasZeroMoralPoint(): bool
    {
        return $this->getMoralPoint() === 0;
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
        return $this->playerVariables->getValueByName(PlayerVariableEnum::TRIUMPH);
    }

    public function setTriumph(int $triumph): static
    {
        $this->playerVariables->setValueByName($triumph, PlayerVariableEnum::TRIUMPH);
        $this->playerInfo->getClosedPlayer()->setTriumph($triumph);

        return $this;
    }

    public function addTriumph(int $triumph): static
    {
        $this->playerVariables->changeValueByName($triumph, PlayerVariableEnum::TRIUMPH);
        $this->playerInfo->getClosedPlayer()->setTriumph($this->getTriumph());

        return $this;
    }

    public function getMaxPrivateChannels(): int
    {
        return $this->getVariableByName(PlayerVariableEnum::PRIVATE_CHANNELS)->getMaxValueOrThrow();
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

    public function isInARoom(): bool
    {
        return $this->getPlace()->getType() === PlaceTypeEnum::ROOM;
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

    public function doesNotHaveTitle(string $title): bool
    {
        return $this->hasTitle($title) === false;
    }

    public function removeAllTitles(): static
    {
        $this->titles = [];

        return $this;
    }

    public function getExplorationOrThrow(): Exploration
    {
        if ($this->exploration === null) {
            throw new \RuntimeException('The player is not exploring');
        }

        return $this->exploration;
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

    /** @return Collection<int, Message> */
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
        $min = $this->daedalus->getAlivePlayers()->hasPlayerWithSkill(SkillEnum::NERON_ONLY_FRIEND) && $project->isNeronProject() ? $max : $this->getMinEfficiencyForProject($project);

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
        $providedSkillActions = [];

        /** @var Skill $skill */
        foreach ($this->getSkills() as $skill) {
            $providedSkillActions = array_merge($providedSkillActions, $skill->getProvidedActions($actionTarget, $actionRanges)->toArray());
        }
        $providedSkillActions = $this->removeDuplicateActions($providedSkillActions);

        $actions = array_merge($actions, $providedSkillActions);

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
        return $this->getName() === CharacterEnum::NULL;
    }

    public function isDead(): bool
    {
        return $this->isAlive() === false;
    }

    public function getAlivePlayersInRoom(): PlayerCollection
    {
        return $this->getPlace()->getPlayers()->getPlayerAlive();
    }

    public function getAlivePlayersInRoomExceptSelf(): PlayerCollection
    {
        return $this->getPlace()->getPlayers()->getPlayerAlive()->filter(
            fn (Player $player) => $player->notEquals($this)
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
        return $this->lastActionDate <= new \DateTime('-1 day');
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
        return $this->notifications->count() > 0;
    }

    public function hasNotificationByMessage(string $message): bool
    {
        return $this->notifications->filter(static fn (PlayerNotification $notification) => $notification->getMessage() === $message)->count() > 0;
    }

    public function getFirstNotificationOrThrow(): PlayerNotification
    {
        return $this->notifications->first() ?: throw new \RuntimeException('The player does not have a notification');
    }

    public function getNotificationByMessageOrThrow(string $message): PlayerNotification
    {
        return $this->notifications->filter(static fn (PlayerNotification $notification) => $notification->getMessage() === $message)->first() ?: throw new \RuntimeException("The player does not have a notification with message: {$message}");
    }

    public function addNotification(PlayerNotification $notification): self
    {
        $this->notifications->add($notification);

        return $this;
    }

    public function deleteNotification(): self
    {
        $this->notifications->removeElement($this->notifications->first());

        return $this;
    }

    public function deleteNotificationByMessage(string $message): self
    {
        $this->notifications->removeElement($this->notifications->filter(static fn (PlayerNotification $notification) => $notification->getMessage() === $message)->first());

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isInactive() === false;
    }

    public function isInactive(): bool
    {
        return $this->hasAnyStatuses([PlayerStatusEnum::INACTIVE, PlayerStatusEnum::HIGHLY_INACTIVE]);
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
        return $plant->isAPlant() && $this->hasSkill(SkillEnum::BOTANIST);
    }

    public function shouldBeHurtByShower(): bool
    {
        return $this->isMush() && $this->doesNotHaveSkill(SkillEnum::SPLASHPROOF);
    }

    public function getHumanSkillSlots(): int
    {
        $skillSlots = $this->daedalus->getDaedalusConfig()->getHumanSkillSlots();

        return $this->hasStatus(PlayerStatusEnum::HAS_READ_MAGE_BOOK) ? $skillSlots + 1 : $skillSlots;
    }

    public function hasFilledTheirHumanSkillSlots(): bool
    {
        return $this->getHumanSkills()->count() === $this->getHumanSkillSlots();
    }

    public function hasFilledTheirMushSkillSlots(): bool
    {
        return $this->getMushSkills()->count() === $this->daedalus->getDaedalusConfig()->getMushSkillSlots();
    }

    public function addReceivedMission(CommanderMission $mission): static
    {
        $this->receivedMissions->add($mission);

        return $this;
    }

    public function getReceivedMissions(): ArrayCollection
    {
        return new ArrayCollection($this->receivedMissions->toArray());
    }

    public function hasPendingMissions(): bool
    {
        return $this->receivedMissions->filter(static fn (CommanderMission $mission) => $mission->isPending())->count() > 0;
    }

    public function hasMeansOfCommunication(): bool
    {
        return $this->hasOperationalEquipmentByName(ItemEnum::WALKIE_TALKIE)
            || $this->hasOperationalEquipmentByName(ItemEnum::ITRACKIE)
            || $this->hasStatus(PlayerStatusEnum::BRAINSYNC)
            || $this->getPlace()->getName() === RoomEnum::BRIDGE
            || $this->hasTitle(TitleEnum::COM_MANAGER);
    }

    public function hasATalkie(): bool
    {
        return $this->hasAnyOperationalEquipment([ItemEnum::WALKIE_TALKIE, ItemEnum::ITRACKIE]);
    }

    public function getOldPlaceOrThrow(): Place
    {
        $place = $this->getStatusByNameOrThrow(PlayerStatusEnum::PREVIOUS_ROOM)->getTarget();

        return $place instanceof Place ? $place : throw new \RuntimeException('The player does not have a previous room');
    }

    public function flagAsAlphaMush(): static
    {
        $this->playerInfo->getClosedPlayer()->flagAsAlphaMush();

        return $this;
    }

    public function isAlphaMush(): bool
    {
        return $this->playerInfo->getClosedPlayer()->isAlphaMush();
    }

    public function isMale(): bool
    {
        return CharacterEnum::isMale($this->getName());
    }

    public function getGender(): string
    {
        return $this->isMale() ? 'male' : 'female';
    }

    public function isTheOnlyGuardianInTheRoom(): bool
    {
        return $this->getPlace()->getAlivePlayersExcept($this)->hasPlayerWithStatus(PlayerStatusEnum::GUARDIAN) === false;
    }

    public function canAccessMushChannel(): bool
    {
        return $this->isMush() || $this->hasPheromodemConnectedTracker();
    }

    public function shouldNotCatchDisease(DiseaseConfig $diseaseConfig, D100RollServiceInterface $d100Roll): bool
    {
        $hygienistResistsDisease = $diseaseConfig->isPhysicalDisease() && $this->hasSkill(SkillEnum::HYGIENIST) && $d100Roll->isSuccessful($this->hygienistBonus());
        $mushResistsDisease = $this->isMush() && $diseaseConfig->isNotAnInjury();

        return $hygienistResistsDisease || $mushResistsDisease;
    }

    public function isMute(): bool
    {
        return $this->hasModifierByModifierName(ModifierNameEnum::MUTE_PREVENT_MESSAGES_MODIFIER);
    }

    public function shouldBeAnonymous($tags): bool
    {
        return \in_array(ActionEnum::HIT->value, $tags, true) && $this->hasSkill(SkillEnum::NINJA);
    }

    public function canTradePlayer(self $player): bool
    {
        // If player is dead, they cannot be traded
        if ($player->isDead()) {
            return false;
        }

        // Player is always tradable if highly inactive
        if ($player->hasStatus(PlayerStatusEnum::HIGHLY_INACTIVE)) {
            return true;
        }

        // Player must be in a storage to be tradable
        if ($player->isNotInAny(RoomEnum::getStorages())) {
            return false;
        }

        // Player in storage is tradable if trader is Mush or player is inactive
        return $this->isMush() || $player->isInactive();
    }

    public function hasStandaloneSkill(SkillEnum $skillName): bool
    {
        return $this->getSkills()->exists(static fn ($_, Skill $skill) => $skill->getName() === $skillName);
    }

    public function addPlayerHighlight(PlayerHighlight $playerHighlight): static
    {
        $this->playerInfo->addPlayerHighlight($playerHighlight);

        return $this;
    }

    private function hasPheromodemConnectedTracker(): bool
    {
        $hasTracker = $this->hasOperationalEquipmentByName(ItemEnum::ITRACKIE) || $this->hasOperationalEquipmentByName(ItemEnum::TRACKER);
        $pheromodemIsFinished = $this->daedalus->getProjectByName(ProjectName::PHEROMODEM)->isFinished();

        return $hasTracker && $pheromodemIsFinished;
    }

    private function getScalableEfficiencyForProject(Project $project): int
    {
        $efficiency = $this->getEfficiencyWithBonusSkills($project->getEfficiency(), $project);
        $efficiency = $this->getEfficiencyWithParticipationMalus($efficiency, $project);

        return $this->getEfficiencyWithCpuPriorityBonus($efficiency, $project);
    }

    private function getMinEfficiencyForProject(Project $project): int
    {
        if ($this->hasStatus(PlayerStatusEnum::GENIUS_IDEA) && $project->isNotPilgred()) {
            return 100;
        }

        $efficiency = $this->getScalableEfficiencyForProject($project);

        return min(100, max(0, $this->getEfficiencyWithExternalItems($efficiency, $project)));
    }

    private function getMaxEfficiencyForProject(Project $project): int
    {
        if ($this->hasStatus(PlayerStatusEnum::GENIUS_IDEA) && $project->isNotPilgred()) {
            return 100;
        }

        $efficiency = $this->getScalableEfficiencyForProject($project);
        $efficiency = (int) ($efficiency + $efficiency / 2);
        $efficiency = $this->getEfficiencyWithExternalItems($efficiency, $project);

        return max(0, min($efficiency, 100));
    }

    private function getEfficiencyWithBonusSkills(int $efficiency, Project $project): int
    {
        $numberOfSkillsMatching = $this->getNumberOfMatchingSkills($project->getBonusSkills());

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
        if ($this->daedalus->isCpuPriorityOn(NeronCpuPriorityEnum::RESEARCH) && $project->isResearchProject()) {
            return $efficiency + Project::CPU_PRIORITY_BONUS;
        }

        return $efficiency;
    }

    private function getEfficiencyWithExternalItems(int $efficiency, Project $project): int
    {
        if ($project->isResearchProject() && $this->daedalus->getPlaceByNameOrThrow(RoomEnum::NEXUS)->hasEquipmentByName(GearItemEnum::PRINTED_CIRCUIT_JELLY)) {
            return $efficiency + Project::PRINTED_CIRCUIT_JELLY;
        }

        return $efficiency;
    }

    private function getSkillByNameOrNull(SkillEnum $name): ?Skill
    {
        $skill = $this->getSkills()->filter(static fn (Skill $skill) => $skill->getName() === $name)->first();

        return $skill ?: null;
    }

    private function canReadRationProperties(GameEquipment $food): bool
    {
        $isChefReadingNonDrug = $this->hasSkill(SkillEnum::CHEF) && !$food->isADrug();

        return $food->isARation() && ($this->isMush() || $isChefReadingNonDrug);
    }

    private function canReadFruitProperties(GameEquipment $food): bool
    {
        return $food->isAFruit() && $this->hasSkill(SkillEnum::BOTANIST);
    }

    private function canReadDrugProperties(GameEquipment $food): bool
    {
        return $food->isADrug() && $this->hasAnySkill([SkillEnum::NURSE, SkillEnum::BIOLOGIST, SkillEnum::MEDIC]);
    }

    private function removeDuplicateActions(array $actions): array
    {
        for ($key = 1; $key < \count($actions); ++$key) {
            $action = $actions[$key];
            $previousAction = $actions[$key - 1];
            if ($action->equals($previousAction)) {
                unset($actions[$key]);
            }
        }

        return $actions;
    }

    private function hygienistBonus(): int
    {
        return (int) $this
            ->getModifiers()
            ->getModifierByModifierNameOrThrow(ModifierNameEnum::HYGIENIST_DISEASE_MODIFIER)
            ->getVariableModifierConfigOrThrow()
            ->getDelta();
    }

    /** @param array<SkillEnum> $expectedSkills */
    private function getNumberOfMatchingSkills(array $expectedSkills): int
    {
        $result = 0;
        foreach ($expectedSkills as $skill) {
            if ($this->hasSkill($skill)) {
                ++$result;
            }
        }

        return $result;
    }

    private function hasSkillThroughPolyvalent(SkillEnum $skillName): bool
    {
        return $skillName->isPolyvalentSkill() && $this->getSkills()->exists(static fn ($_, Skill $skill) => $skill->getName() === SkillEnum::POLYVALENT);
    }
}
