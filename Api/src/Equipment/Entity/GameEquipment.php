<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionHolderInterface;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Hunter\Entity\HunterTargetEntityInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierHolderTrait;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'game_equipment' => GameEquipment::class,
    'door' => Door::class,
    'game_item' => GameItem::class,
    'drone' => Drone::class,
])]
class GameEquipment implements StatusHolderInterface, LogParameterInterface, ModifierHolderInterface, HunterTargetEntityInterface, ActionHolderInterface, ActionProviderInterface, ModifierProviderInterface
{
    use ModifierHolderTrait;
    use TargetStatusTrait;
    use TimestampableEntity;

    #[ORM\ManyToOne(targetEntity: Place::class, inversedBy: 'equipments')]
    protected ?Place $place = null;

    #[ORM\ManyToOne(targetEntity: EquipmentConfig::class)]
    protected EquipmentConfig $equipment;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToMany(mappedBy: 'gameEquipment', targetEntity: StatusTarget::class, cascade: ['ALL'])]
    private Collection $statuses;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'gameEquipment', targetEntity: ModifierHolder::class, cascade: ['REMOVE'])]
    private Collection $modifiers;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $owner = null;

    public function __construct(
        EquipmentHolderInterface $equipmentHolder,
    ) {
        $this->statuses = new ArrayCollection();
        $this->modifiers = new ModifierCollection();

        if ($equipmentHolder instanceof Place) {
            $this->place = $equipmentHolder;
            $equipmentHolder->addEquipment($this);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClassName(): string
    {
        return static::class;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->getStatuses()->contains($status)) {
            if (!$statusTarget = $status->getStatusTargetTarget()) {
                $statusTarget = new StatusTarget();
            }
            $statusTarget->setOwner($status);
            $statusTarget->setGameEquipment($this);
            $this->statuses->add($statusTarget);
        }

        return $this;
    }

    public function getPlace(): Place
    {
        $place = $this->place;
        if ($place === null) {
            throw new \LogicException("Cannot find place of the GameEquipment {$this->name}");
        }

        return $place;
    }

    public function getHolder(): EquipmentHolderInterface
    {
        if ($this->place === null) {
            throw new \RuntimeException("Equipment {$this->name} should have a holder");
        }

        return $this->place;
    }

    public function setHolder(EquipmentHolderInterface $holder): static
    {
        if (!$holder instanceof Place) {
            throw new UnexpectedTypeException($holder, Place::class);
        }

        if ($holder !== ($oldPlace = $this->getHolder())) {
            $oldPlace->removeEquipment($this);

            $this->place = $holder;
            $holder->addEquipment($this);
        }

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->getHolder()->getDaedalus();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEquipment(): EquipmentConfig
    {
        return $this->equipment;
    }

    public function setEquipment(EquipmentConfig $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getAllModifiers(): ModifierCollection
    {
        $allModifiers = $this->getModifiers();

        /** @var Player $player */
        if (($player = $this->getHolder()) instanceof Player) {
            $allModifiers = $allModifiers->addModifiers($player->getModifiers());
        }
        $allModifiers = $allModifiers->addModifiers($this->getPlace()->getModifiers());

        return $allModifiers->addModifiers($this->getDaedalus()->getModifiers());
    }

    public function getOwner(): ?Player
    {
        return $this->owner;
    }

    public function setOwner(Player $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function isBroken(): bool
    {
        return $this
            ->getStatuses()
            ->exists(static fn (int $key, Status $status) => ($status->getName() === EquipmentStatusEnum::BROKEN));
    }

    /**
     * Checks if the equipment is operational: : it is not broken and has charges remaining.
     */
    public function isOperational(): bool
    {
        /** @var Status $status */
        foreach ($this->getStatuses() as $status) {
            if (\in_array($status->getStatusConfig()->getStatusName(), EquipmentStatusEnum::getOutOfOrderStatuses(), true)) {
                return false;
            }
            if ($status instanceof ChargeStatus
                && !$status->isCharged()
                && $status->getStatusConfig()->getStatusName() === EquipmentStatusEnum::ELECTRIC_CHARGES
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the equipment is not operational: it is broken or has no charges remaining.
     */
    public function isNotOperational(): bool
    {
        return !$this->isOperational();
    }

    public function isBreakable(): bool
    {
        return $this->getEquipment()->isBreakable();
    }

    public function shouldTriggerRoomTrap(): bool
    {
        return true;
    }

    public function getLogName(): string
    {
        return $this->getName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::EQUIPMENT;
    }

    public function getGameEquipment(): ?self
    {
        return $this;
    }

    public function getPlayer(): ?Player
    {
        $holder = $this->getHolder();
        if ($holder instanceof Player) {
            return $holder;
        }

        return null;
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

    public function getOperationalStatus(string $actionName): ActionProviderOperationalStateEnum
    {
        if ($this->isBroken() && $this->isActionProvidedByMechanic($actionName)) {
            return ActionProviderOperationalStateEnum::BROKEN;
        }

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

    // return actions provided by this entity and the other actionProviders it bears
    public function getProvidedActions(ActionHolderEnum $actionTarget, array $actionRanges): Collection
    {
        $actions = [];

        /** @var ActionConfig $actionConfig */
        foreach ($this->getEquipment()->getActionConfigs() as $actionConfig) {
            if (
                $actionConfig->getDisplayHolder() === $actionTarget
                && \in_array($actionConfig->getRange(), $actionRanges, true)
            ) {
                $action = new Action();
                $action->setActionProvider($this)->setActionConfig($actionConfig);
                $actions[] = $action;
            }
        }

        // add actions provided by the statuses
        $allActions = [];

        /** @var Status $status */
        foreach ($this->getStatuses() as $status) {
            $allActions[] = $status->getProvidedActions($actionTarget, $actionRanges)->toArray();
        }

        return new ArrayCollection(array_merge($actions, ...$allActions));
    }

    /**
     * Return action available for this target $actionTarget should be set to game_equipment.
     */
    public function getActions(Player $activePlayer, ?ActionHolderEnum $actionTarget = null): Collection
    {
        if ($actionTarget === null) {
            throw new \RuntimeException('You must specify if the action holder is equipment or terminal');
        }

        // first actions provided by the gameEquipment itself
        $actions = $this->getProvidedActions($actionTarget, [ActionRangeEnum::SELF])->toArray();

        // then actions provided by the room
        $actions = array_merge($actions, $this->getPlace()->getProvidedActions(
            $actionTarget,
            [ActionRangeEnum::ROOM, ActionRangeEnum::SHELF]
        )->toArray());

        // then actions provided by the active player
        $actions = array_merge($actions, $activePlayer->getProvidedActions(
            $actionTarget,
            [ActionRangeEnum::PLAYER]
        )->toArray());

        return new ArrayCollection($actions);
    }

    public function getActionConfigByNameOrNull(ActionEnum $actionName): ?ActionConfig
    {
        /** @var ActionConfig $actionConfig */
        foreach ($this->equipment->getActionConfigs() as $actionConfig) {
            if ($actionConfig->getActionName() === $actionName) {
                return $actionConfig;
            }
        }

        return null;
    }

    public function getActionConfigByNameOrThrow(ActionEnum $actionName): ActionConfig
    {
        $actionConfig = $this->getActionConfigByNameOrNull($actionName);
        if ($actionConfig === null) {
            throw new \RuntimeException("Action {$actionName->value} not found in the actions of {$this->name} equipment.");
        }

        return $actionConfig;
    }

    public function canPlayerReach(Player $player): bool
    {
        return $this->getPlace() === $player->getPlace();
    }

    public function getMechanicActionByNameOrThrow(ActionEnum $actionName): ActionConfig
    {
        foreach ($this->getEquipment()->getMechanics() as $mechanic) {
            foreach ($mechanic->getActions() as $action) {
                if ($action->getActionName() === $actionName) {
                    return $action;
                }
            }
        }

        throw new \RuntimeException("Action {$actionName->value} not found in the mechanics of {$this->name} equipment.");
    }

    public function getNormalizationType(): string
    {
        return LogParameterKeyEnum::EQUIPMENT . 's';
    }

    public function getMechanicByNameOrThrow(string $mechanicName): EquipmentMechanic
    {
        return $this->getMechanicByNameOrNull($mechanicName) ?? throw new \RuntimeException("Mechanic {$mechanicName} not found in the mechanics of {$this->name} equipment.");
    }

    public function getWeaponMechanicOrThrow(): Weapon
    {
        $weapon = $this->getMechanicByNameOrThrow(EquipmentMechanicEnum::WEAPON);

        return $weapon instanceof Weapon ? $weapon : throw new \RuntimeException("Equipment {$this->name} does not have a weapon mechanic.");
    }

    public function hasMechanicByName(string $mechanicName): bool
    {
        foreach ($this->getEquipment()->getMechanics() as $mechanic) {
            if (\in_array($mechanicName, $mechanic->getMechanics(), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function shouldBeTransformedIntoStandardRation(): bool
    {
        return (new ArrayCollection([GameRationEnum::COOKED_RATION, GameRationEnum::ALIEN_STEAK]))->contains($this->getName());
    }

    public function isInPlaceByName(string $place): bool
    {
        return $this->getPlace() === $this->getDaedalus()->getPlaceByName($place);
    }

    public function getCurrentJukeboxPlayer(): ?Player
    {
        $song = $this->getStatusByName(EquipmentStatusEnum::JUKEBOX_SONG);
        $target = $song?->getTarget();
        if (!$target) {
            return null;
        }

        return $this->getDaedalus()->getPlayerByName($target->getName());
    }

    public function updateSongWithPlayerFavorite(Player $player): void
    {
        $song = $this->getStatusByNameOrThrow(EquipmentStatusEnum::JUKEBOX_SONG);

        $song->setTarget($player);
    }

    public function currentSongMatchesPlayerFavorite(Player $player): bool
    {
        $song = $this->getStatusByName(EquipmentStatusEnum::JUKEBOX_SONG);

        return $song?->getTarget()?->equals($player) ?? false;
    }

    /**
     * @return ArrayCollection<int, string>
     */
    public function getAllMechanicsAndEquipmentName(): ArrayCollection
    {
        $names = [];
        foreach ($this->getEquipment()->getMechanics() as $mechanic) {
            $names[] = $mechanic->getMechanics();
        }

        return new ArrayCollection(array_merge([$this->getName()], ...$names));
    }

    public function isARation(): bool
    {
        return $this->hasMechanicByName(EquipmentMechanicEnum::RATION);
    }

    public function isAFruit(): bool
    {
        return $this->hasMechanicByName(EquipmentMechanicEnum::FRUIT);
    }

    public function isAPlant(): bool
    {
        return $this->hasMechanicByName(EquipmentMechanicEnum::PLANT);
    }

    public function isADrug(): bool
    {
        return $this->hasMechanicByName(EquipmentMechanicEnum::DRUG);
    }

    public function getFruitProduction(): int
    {
        return $this->canProduceFruit() ? 1 : 0;
    }

    public function getOxygenProduction(): int
    {
        return $this->canProduceOxygen() ? 1 : 0;
    }

    public function isYoungPlant(): bool
    {
        return $this->hasStatus(EquipmentStatusEnum::PLANT_YOUNG);
    }

    public function getMaturationTimeLeftOrThrow(): int
    {
        $maturationTime = $this->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG)->getMaturationTimeOrThrow();
        $plantAge = $this->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG)->getCharge();

        $maturationTimeLeft = $maturationTime - $plantAge;
        if ($this->getPlace()->hasOperationalEquipmentByName(EquipmentEnum::HYDROPONIC_INCUBATOR)) {
            $maturationTimeLeft = (int) ceil($maturationTimeLeft / 2);
        }

        return max(0, $maturationTimeLeft);
    }

    public function getAllModifierConfigs(): ArrayCollection
    {
        $modifierConfigs = [];

        // then modifiers provided by equipment statuses
        $modifierConfigs = $this->getStatuses()
            ->map(static fn (Status $status) => $status->getStatusConfig()->getModifierConfigs())
            ->reduce(static fn (array $modifierConfigs, $statusModifierConfigs) => array_merge($modifierConfigs, $statusModifierConfigs->toArray()), $modifierConfigs);

        // then modifiers provided by gear
        if ($this->hasMechanicByName(EquipmentMechanicEnum::GEAR)) {
            /** @var Gear $gear */
            $gear = $this->getMechanicByNameOrThrow(EquipmentMechanicEnum::GEAR);

            $modifierConfigs = array_merge($gear->getModifierConfigs()->toArray(), $modifierConfigs);
        }

        return new ArrayCollection($modifierConfigs);
    }

    private function canProduceFruit(): bool
    {
        foreach ([EquipmentStatusEnum::PLANT_YOUNG, EquipmentStatusEnum::PLANT_DRY, EquipmentStatusEnum::PLANT_DISEASED, EquipmentStatusEnum::PLANT_THIRSTY] as $status) {
            if ($this->hasStatus($status)) {
                return false;
            }
        }

        return true;
    }

    private function canProduceOxygen(): bool
    {
        foreach ([EquipmentStatusEnum::PLANT_YOUNG, EquipmentStatusEnum::PLANT_DRY, EquipmentStatusEnum::PLANT_DISEASED] as $status) {
            if ($this->hasStatus($status)) {
                return false;
            }
        }

        return true;
    }

    private function isActionProvidedByMechanic(string $actionName): bool
    {
        $actionEnum = ActionEnum::tryFrom($actionName);
        if ($actionEnum !== null) {
            return $this->getToolMechanicOrNull()?->hasAction($actionEnum) ?: false;
        }

        return $this->getGearMechanicOrNull()?->hasModifierConfigByModifierName($actionName) ?: false;
    }

    private function getGearMechanicOrNull(): ?Gear
    {
        $gear = $this->getMechanicByNameOrNull(EquipmentMechanicEnum::GEAR);

        return $gear instanceof Gear ? $gear : null;
    }

    private function getToolMechanicOrNull(): ?Tool
    {
        $tool = $this->getMechanicByNameOrNull(EquipmentMechanicEnum::TOOL);

        return $tool instanceof Tool ? $tool : null;
    }

    private function getMechanicByNameOrNull(string $mechanicName): ?EquipmentMechanic
    {
        foreach ($this->getEquipment()->getMechanics() as $mechanic) {
            if (\in_array($mechanicName, $mechanic->getMechanics(), true)) {
                return $mechanic;
            }
        }

        return null;
    }
}
