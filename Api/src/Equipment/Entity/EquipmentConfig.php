<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\GameConfig;

/**
 * Class EquipmentConfig.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "equipment_config" = "Mush\Equipment\Entity\EquipmentConfig",
 *     "item_config" = "Mush\Equipment\Entity\ItemConfig"
 * })
 */
class EquipmentConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig", inversedBy="equipmentsConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Equipment\Entity\EquipmentMechanic")
     */
    private Collection $mechanics;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isBreakable = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isFireDestroyable = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isFireBreakable = false;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $dismountedProducts = [];

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Action\Entity\Action")
     */
    private Collection $actions;

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Status\Entity\Config\StatusConfig")
     */
    private Collection $initStatus;

    public function __construct()
    {
        $this->mechanics = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->initStatus = new ArrayCollection();
    }

    public function createGameEquipment(): GameEquipment
    {
        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setName($this->getName())
            ->setEquipment($this)
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

    /**
     * @return static
     */
    public function setGameConfig(GameConfig $gameConfig): self
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return static
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMechanics(): Collection
    {
        return $this->mechanics;
    }

    /**
     * @return static
     */
    public function setMechanics(Collection $mechanics): self
    {
        $this->mechanics = $mechanics;

        return $this;
    }

    public function getMechanicByName(string $mechanic): ?EquipmentMechanic
    {
        $equipmentMechanics = $this->mechanics->filter(fn (EquipmentMechanic $equipmentMechanic) => ($equipmentMechanic->getMechanic() === $mechanic));

        return $equipmentMechanics->count() > 0 ? $equipmentMechanics->first() : null;
    }

    public function getMechanicByMechanics(array $mechanics): ?EquipmentMechanic
    {
        $equipmentMechanics = $this->mechanics->filter(fn (EquipmentMechanic $equipmentMechanic) => (in_array($equipmentMechanic->getMechanic(), $mechanics)));

        return $equipmentMechanics->count() > 0 ? $equipmentMechanics->first() : null;
    }

    public function getRationsMechanic(): ?Ration
    {
        $mechanic = $this->getMechanicByMechanics([EquipmentMechanicEnum::RATION, EquipmentMechanicEnum::FRUIT, EquipmentMechanicEnum::DRUG]);

        if ($mechanic !== null && !$mechanic instanceof Ration) {
            throw new \LogicException('This should be a ration');
        }

        return $mechanic;
    }

    public function isFireDestroyable(): bool
    {
        return $this->isFireDestroyable;
    }

    /**
     * @return static
     */
    public function setIsFireDestroyable(bool $isFireDestroyable): self
    {
        $this->isFireDestroyable = $isFireDestroyable;

        return $this;
    }

    public function isFireBreakable(): bool
    {
        return $this->isFireBreakable;
    }

    /**
     * @return static
     */
    public function setIsFireBreakable(bool $isFireBreakable): self
    {
        $this->isFireBreakable = $isFireBreakable;

        return $this;
    }

    public function isBreakable(): bool
    {
        return $this->isBreakable;
    }

    /**
     * @return static
     */
    public function setIsBreakable(bool $isBreakable): self
    {
        $this->isBreakable = $isBreakable;

        return $this;
    }

    /**
     * @return static
     */
    public function setActions(Collection $actions): self
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

    /**
     * @return static
     */
    public function setInitStatus(Collection $initStatus): self
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

    /**
     * @return static
     */
    public function setDismountedProducts(array $dismountedProducts): self
    {
        $this->dismountedProducts = $dismountedProducts;

        return $this;
    }
}
