<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'equipment_config' => EquipmentConfig::class,
    'item_config' => ItemConfig::class,
])]
class EquipmentConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\ManyToMany(targetEntity: GameConfig::class, inversedBy: 'equipmentsConfig')]
    private Collection $gameConfig;

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

    #[ORM\ManyToMany(targetEntity: Action::class)]
    private Collection $actions;

    #[ORM\ManyToMany(targetEntity: StatusConfig::class)]
    private Collection $initStatuses;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isPersonal = false;

    public function __construct()
    {
        $this->mechanics = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->initStatuses = new ArrayCollection();
        $this->gameConfig = new ArrayCollection();
    }

    public function createGameEquipment(EquipmentHolderInterface $holder): GameEquipment
    {
        $gameEquipment = new GameEquipment($holder);
        $gameEquipment
            ->setName($this->getEquipmentShortName())
            ->setEquipment($this)
        ;

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
        } elseif ($this->getMechanicByName(EquipmentMechanicEnum::BOOK)) {
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
     * @psalm-param ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Blueprint>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Book>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Document>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Drug>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Fruit>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Gear>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Plant>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Ration>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Tool>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Weapon>|ArrayCollection<int, \Mush\Equipment\Entity\Mechanics\Gear|\Mush\Equipment\Entity\Mechanics\Tool> $mechanics
     */
    public function setMechanics(Collection|array $mechanics): static
    {
        if (is_array($mechanics)) {
            $mechanics = new ArrayCollection($mechanics);
        }

        $this->mechanics = $mechanics;

        return $this;
    }

    public function getMechanicByName(string $mechanic): ?EquipmentMechanic
    {
        $equipmentMechanics = $this->mechanics->filter(fn (EquipmentMechanic $equipmentMechanic) => in_array($mechanic, $equipmentMechanic->getMechanics()));

        return $equipmentMechanics->count() > 0 ? $equipmentMechanics->first() : null;
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
     * @param Collection<int, Action> $actions
     */
    public function setActions(Collection|array $actions): static
    {
        if (is_array($actions)) {
            $actions = new ArrayCollection($actions);
        }

        $this->actions = $actions;

        return $this;
    }

    public function getActions(): Collection
    {
        $actions = $this->actions->toArray();

        /** @var EquipmentMechanic $mechanic */
        foreach ($this->getMechanics() as $mechanic) {
            $actions = array_merge($actions, $mechanic->getActions()->toArray());
        }

        return new ArrayCollection($actions);
    }

    /**
     * @psalm-param ArrayCollection<int, ChargeStatusConfig>|ArrayCollection<int, StatusConfig> $initStatuses
     */
    public function setInitStatuses(ArrayCollection|array $initStatuses): static
    {
        if (is_array($initStatuses)) {
            $initStatuses = new ArrayCollection($initStatuses);
        }

        $this->initStatuses = $initStatuses;

        return $this;
    }

    public function getInitStatuses(): Collection
    {
        return $this->initStatuses;
    }

    public function hasAction(string $actionName): bool
    {
        return $this->getActions()->exists(fn (int $id, Action $action) => $action->getActionName() === $actionName);
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
