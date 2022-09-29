<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\ConfigInterface;
use Mush\Game\Entity\GameConfig;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'equipment_config' => EquipmentConfig::class,
    'item_config' => ItemConfig::class,
])]
class EquipmentConfig implements ConfigInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: GameConfig::class, inversedBy: 'equipmentsConfig')]
    private GameConfig $gameConfig;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

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
    private Collection $initStatus;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isPersonal = false;

    public function __construct()
    {
        $this->mechanics = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->initStatus = new ArrayCollection();
    }

    public function createGameEquipment(): Equipment
    {
        $gameEquipment = new Equipment();
        $gameEquipment
            ->setName($this->getShortName())
            ->setConfig($this)
        ;

        return $gameEquipment;
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortName(): string
    {
        if ($this->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT)) {
            return ItemEnum::BLUEPRINT;
        } elseif ($this->getMechanicByName(EquipmentMechanicEnum::BOOK)) {
            return ItemEnum::APPRENTON;
        }

        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMechanics(): Collection
    {
        return $this->mechanics;
    }

    public function setMechanics(Collection $mechanics): static
    {
        $this->mechanics = $mechanics;

        return $this;
    }

    public function getMechanicByName(string $mechanic): ?EquipmentMechanic
    {
        $equipmentMechanics = $this->mechanics->filter(fn (EquipmentMechanic $equipmentMechanic) => in_array($mechanic, $equipmentMechanic->getMechanics()));

        return $equipmentMechanics->count() > 0 ? $equipmentMechanics->first() : null;
    }

    public function isFireDestroyable(): bool
    {
        return $this->isFireDestroyable;
    }

    public function setIsFireDestroyable(bool $isFireDestroyable): static
    {
        $this->isFireDestroyable = $isFireDestroyable;

        return $this;
    }

    public function isFireBreakable(): bool
    {
        return $this->isFireBreakable;
    }

    public function setIsFireBreakable(bool $isFireBreakable): static
    {
        $this->isFireBreakable = $isFireBreakable;

        return $this;
    }

    public function isBreakable(): bool
    {
        return $this->isBreakable;
    }

    public function setIsBreakable(bool $isBreakable): static
    {
        $this->isBreakable = $isBreakable;

        return $this;
    }

    public function setActions(Collection $actions): static
    {
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

    public function setInitStatus(Collection $initStatus): static
    {
        $this->initStatus = $initStatus;

        return $this;
    }

    public function getInitStatus(): Collection
    {
        return $this->initStatus;
    }

    public function hasAction(string $actionName): bool
    {
        return $this->getActions()->exists(fn (int $id, Action $action) => $action->getName() === $actionName);
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

    public function isPersonal(): bool
    {
        return $this->isPersonal;
    }

    public function setIsPersonal(bool $isPersonal): static
    {
        $this->isPersonal = $isPersonal;

        return $this;
    }
}
