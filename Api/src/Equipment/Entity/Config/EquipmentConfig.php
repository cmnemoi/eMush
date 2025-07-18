<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\BreakableTypeEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\MetaGame\Entity\Skin\SkinableConfigInterface;
use Mush\MetaGame\Entity\Skin\SkinSlotConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'equipment_config' => EquipmentConfig::class,
    'item_config' => ItemConfig::class,
    'drone_config' => DroneConfig::class,
    'space_ship_config' => SpaceShipConfig::class,
])]
class EquipmentConfig implements SkinableConfigInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $equipmentName;

    #[ORM\ManyToMany(targetEntity: EquipmentMechanic::class)]
    private Collection $mechanics;

    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: BreakableTypeEnum::class, options: ['default' => BreakableTypeEnum::NONE])]
    private BreakableTypeEnum $breakableType = BreakableTypeEnum::NONE;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $dismountedProducts = [];

    #[ORM\ManyToMany(targetEntity: ActionConfig::class)]
    private Collection $actionConfigs;

    #[ORM\ManyToMany(targetEntity: StatusConfig::class)]
    private Collection $initStatuses;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isPersonal = false;

    #[ORM\ManyToMany(targetEntity: SkinSlotConfig::class, cascade: ['REMOVE'], orphanRemoval: true)]
    private Collection $skinSlotsConfig;

    public function __construct()
    {
        $this->mechanics = new ArrayCollection();
        $this->actionConfigs = new ArrayCollection();
        $this->initStatuses = new ArrayCollection();
        $this->skinSlotsConfig = new ArrayCollection();
    }

    public static function fromConfigData(array $configData): self
    {
        $config = new self();

        $config
            ->setName($configData['name'])
            ->setEquipmentName($configData['equipmentName'])
            ->setBreakableType($configData['breakableType'])
            ->setDismountedProducts($configData['dismountedProducts'])
            ->setIsPersonal($configData['isPersonal']);

        return $config;
    }

    public function createGameEquipment(EquipmentHolderInterface $holder): GameEquipment
    {
        // Do not allow GameEquipment holders to be players
        $holder = $holder instanceof Player ? $holder->getPlace() : $holder;
        if (($holder instanceof Place) === false) {
            throw new \InvalidArgumentException('The holder of a GameEquipment must be a Place');
        }

        $gameEquipment = new GameEquipment($holder);
        $gameEquipment
            ->setName($this->getEquipmentShortName())
            ->setEquipment($this)
            ->initializeSkinSlots();

        return $gameEquipment;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEquipmentName(): string
    {
        return $this->equipmentName;
    }

    public function getEquipmentShortName(): string
    {
        if ($this->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT)) {
            return ItemEnum::BLUEPRINT;
        }
        if ($this->getMechanicByName(EquipmentMechanicEnum::BOOK)) {
            return ItemEnum::APPRENTRON;
        }

        return $this->equipmentName;
    }

    public function setEquipmentName(string $equipmentName): static
    {
        $this->equipmentName = $equipmentName;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function buildName(string $configName): self
    {
        $this->name = $this->equipmentName . '_' . $configName;

        return $this;
    }

    /**
     * @return Collection<int, EquipmentMechanic>
     */
    public function getMechanics(): Collection
    {
        return $this->mechanics;
    }

    /**
     * @psalm-param array<array-key, EquipmentMechanic>|ArrayCollection<array-key, EquipmentMechanic> $mechanics
     */
    public function setMechanics(array|Collection $mechanics): static
    {
        if (\is_array($mechanics)) {
            $mechanics = new ArrayCollection($mechanics);
        }

        $this->mechanics = $mechanics;

        return $this;
    }

    public function getMechanicByName(string $mechanic): ?EquipmentMechanic
    {
        $equipmentMechanics = $this->mechanics->filter(static fn (EquipmentMechanic $equipmentMechanic) => \in_array($mechanic, $equipmentMechanic->getMechanics(), true));

        return $equipmentMechanics->first() ?: null;
    }

    public function getMechanicByNameOrThrow(string $mechanic): ?EquipmentMechanic
    {
        $equipmentMechanics = $this->mechanics->filter(static fn (EquipmentMechanic $equipmentMechanic) => \in_array($mechanic, $equipmentMechanic->getMechanics(), true));

        return $equipmentMechanics->first() ?: throw new \RuntimeException("No mechanics with name {$this->name} found.");
    }

    public function getWeaponMechanicOrThrow(): Weapon
    {
        $weapon = $this->getMechanicByNameOrThrow(EquipmentMechanicEnum::WEAPON);

        return $weapon instanceof Weapon ? $weapon : throw new \RuntimeException("Equipment {$this->name} does not have a weapon mechanic.");
    }

    // this is needed for api_platform to work
    public function getBreakableType(): BreakableTypeEnum
    {
        return $this->breakableType;
    }

    public function setBreakableType(BreakableTypeEnum $breakableType): static
    {
        $this->breakableType = $breakableType;

        return $this;
    }

    public function canBeDamaged(): bool
    {
        return $this->breakableType !== BreakableTypeEnum::NONE;
    }

    /**
     * @param array<int, ActionConfig>|Collection<int<0, max>, ActionConfig> $actionConfigs
     */
    public function setActionConfigs(array|Collection $actionConfigs): static
    {
        if (\is_array($actionConfigs)) {
            $actionConfigs = new ArrayCollection($actionConfigs);
        }

        $this->actionConfigs = $actionConfigs;

        return $this;
    }

    public function getActionConfigs(): Collection
    {
        $actions = $this->actionConfigs->toArray();

        $allActions = [];

        /** @var EquipmentMechanic $mechanic */
        foreach ($this->getMechanics() as $mechanic) {
            $allActions[] = $mechanic->getActions()->toArray();
        }

        return new ArrayCollection(array_merge($actions, ...$allActions));
    }

    /**
     * @psalm-param ArrayCollection<array-key, StatusConfig>| array<array-key, StatusConfig> $initStatuses
     */
    public function setInitStatuses(array|ArrayCollection $initStatuses): static
    {
        if (\is_array($initStatuses)) {
            $initStatuses = new ArrayCollection($initStatuses);
        }

        $this->initStatuses = $initStatuses;

        return $this;
    }

    public function getInitStatuses(): Collection
    {
        return $this->initStatuses;
    }

    public function hasAction(ActionEnum $actionName): bool
    {
        return $this->getActionConfigs()->exists(static fn (int $id, ActionConfig $action) => $action->getActionName() === $actionName);
    }

    public function getDismountedProducts(): array
    {
        return $this->dismountedProducts;
    }

    public function setDismountedProducts(array $dismountedProducts): static
    {
        $this->dismountedProducts = $dismountedProducts;

        return $this;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::EQUIPMENT;
    }

    // this is needed for api_platform to work
    public function getIsPersonal(): bool
    {
        return $this->isPersonal;
    }

    public function isPersonal(): bool
    {
        return $this->getIsPersonal();
    }

    public function setIsPersonal(bool $isPersonal): static
    {
        $this->isPersonal = $isPersonal;

        return $this;
    }

    public function getSkinSlotsConfig(): ArrayCollection
    {
        return new ArrayCollection($this->skinSlotsConfig->toArray());
    }

    public function addSkinSlot(SkinSlotConfig $skinSlotConfig): static
    {
        $this->skinSlotsConfig->add($skinSlotConfig);

        return $this;
    }

    public function setSkinSlotsConfig(ArrayCollection $skinSlotsConfig): static
    {
        $this->skinSlotsConfig = $skinSlotsConfig;

        return $this;
    }
}
