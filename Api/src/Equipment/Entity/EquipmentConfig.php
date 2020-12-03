<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class EquipmentConfig.
 *
 * @ORM\Entity
 */
class EquipmentConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     * @ORM\DiscriminatorMap({
     *     "equipment_config" = "Mush\Equipment\Entity\EquipmentConfig",
     *     "item_config" = "Mush\Equipment\Entity\ItemConfig",
     * })
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig", inversedBy="itemsConfig")
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
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $breakableRate = 0;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isFireDestroyable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isFireBreakable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isAlienArtifact = false;

    public function __construct()
    {
        $this->types = new ArrayCollection();
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

    public function setGameConfig(GameConfig $gameConfig): EquipmentConfig
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): EquipmentConfig
    {
        $this->name = $name;

        return $this;
    }

    public function getMechanics(): Collection
    {
        return $this->mechanics;
    }

    public function setMechanics(Collection $mechanics): EquipmentConfig
    {
        $this->mechanics = $mechanics;

        return $this;
    }

    public function getMechanicsByName(string $mechanic): ?EquipmentMechanic
    {
        $equipmentMechanics = $this->mechanics->filter(fn (EquipmentMechanic $equipmentMechanic) => ($equipmentMechanic->getMechanic() === $mechanic));

        return $equipmentMechanics->count() > 0 ? $mechanics->first() : null;
    }

    public function getMechanicByMechanics(array $mechanics): ?EquipmentMechanic
    {
        $equipmentMechanics = $this->mechanics->filter(fn (EquipmentMechanic $equipmentMechanic) => (in_array($equipmentMechanic->getMechanic(), $mechanics)));

        return $equipmentMechanics->count() > 0 ? $equipmentMechanics->first() : null;
    }

    public function getRationsMechanic(): ?Ration
    {
        return $this->getMechanicByMechanics([EquipmentMechanicEnum::RATION, EquipmentMechanicEnum::FRUIT, EquipmentMechanicEnum::DRUG]);
    }

    public function getBreakableRate(): int
    {
        return $this->breakableRate;
    }

    public function setBreakableRate(int $breakableRate): EquipmentConfig
    {
        $this->breakableRate = $breakableRate;

        return $this;
    }

    public function isFireDestroyable(): bool
    {
        return $this->isFireDestroyable;
    }

    public function setIsFireDestroyable(bool $isFireDestroyable): EquipmentConfig
    {
        $this->isFireDestroyable = $isFireDestroyable;

        return $this;
    }

    public function isFireBreakable(): bool
    {
        return $this->isFireBreakable;
    }

    public function setIsFireBreakable(bool $isFireBreakable): EquipmentConfig
    {
        $this->isFireBreakable = $isFireBreakable;

        return $this;
    }

    public function isAlienArtifact(): bool
    {
        return $this->isAlienArtifact;
    }

    public function setIsAlienArtifact(bool $isAlienArtifact): EquipmentConfig
    {
        $this->isAlienArtifact = $isAlienArtifact;

        return $this;
    }

    public function getActions(): Collection
    {
        $actions = ActionEnum::getPermanentEquipmentActions();

        foreach ($this->getMechanics() as $mechanic) {
            $actions = array_merge($actions, $mechanic->getActions());
        }

        return new ArrayCollection($actions);
    }

    public function hasAction(string $action): bool
    {
        return $this->getActions()->contains($action);
    }
}
