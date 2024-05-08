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
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'equipment_config' => EquipmentConfig::class,
    'item_config' => ItemConfig::class,
    'drone_config' => DroneConfig::class,
])]
class EquipmentConfig
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

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isBreakable = false;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isFireDestroyable = false;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isFireBreakable = false;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $dismountedProducts = [];

    #[ORM\ManyToMany(targetEntity: ActionConfig::class)]
    private Collection $actionConfigs;

    #[ORM\ManyToMany(targetEntity: StatusConfig::class)]
    private Collection $initStatuses;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isPersonal = false;

    public function __construct()
    {
        $this->mechanics = new ArrayCollection();
        $this->actionConfigs = new ArrayCollection();
        $this->initStatuses = new ArrayCollection();
    }

    public function createGameEquipment(EquipmentHolderInterface $holder): GameEquipment
    {
        $gameEquipment = new GameEquipment($holder);
        $gameEquipment
            ->setName($this->getEquipmentShortName())
            ->setEquipment($this);

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
            return ItemEnum::APPRENTON;
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

    // this is needed for api_platform to work
    public function getIsFireDestroyable(): bool
    {
        return $this->isFireDestroyable;
    }

    public function isFireDestroyable(): bool
    {
        return $this->getIsFireDestroyable();
    }

    public function setIsFireDestroyable(bool $isFireDestroyable): static
    {
        $this->isFireDestroyable = $isFireDestroyable;

        return $this;
    }

    // this is needed for api_platform to work
    public function getIsFireBreakable(): bool
    {
        return $this->isFireBreakable;
    }

    public function isFireBreakable(): bool
    {
        return $this->getIsFireBreakable();
    }

    public function setIsFireBreakable(bool $isFireBreakable): static
    {
        $this->isFireBreakable = $isFireBreakable;

        return $this;
    }

    // this is needed for api_platform to work
    public function getIsBreakable(): bool
    {
        return $this->isBreakable;
    }

    public function isBreakable(): bool
    {
        return $this->getIsBreakable();
    }

    public function setIsBreakable(bool $isBreakable): static
    {
        $this->isBreakable = $isBreakable;

        return $this;
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

        /** @var EquipmentMechanic $mechanic */
        foreach ($this->getMechanics() as $mechanic) {
            $actions = array_merge($actions, $mechanic->getActions()->toArray());
        }

        return new ArrayCollection($actions);
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
}
