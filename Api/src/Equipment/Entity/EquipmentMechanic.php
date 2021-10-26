<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;

/**
 * Class EquipmentMechanic.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "blueprint" = "Mush\Equipment\Entity\Mechanics\Blueprint",
 *     "book" = "Mush\Equipment\Entity\Mechanics\Book",
 *     "document" = "Mush\Equipment\Entity\Mechanics\Document",
 *     "drug" = "Mush\Equipment\Entity\Mechanics\Drug",
 *     "entity" = "Mush\Equipment\Entity\Mechanics\Entity",
 *     "exploration" = "Mush\Equipment\Entity\Mechanics\Exploration",
 *     "fruit" = "Mush\Equipment\Entity\Mechanics\Fruit",
 *     "gear" = "Mush\Equipment\Entity\Mechanics\Gear",
 *     "plant" = "Mush\Equipment\Entity\Mechanics\Plant",
 *     "ration" = "Mush\Equipment\Entity\Mechanics\Ration",
 *     "tool" = "Mush\Equipment\Entity\Mechanics\Tool",
 *     "weapon" = "Mush\Equipment\Entity\Mechanics\Weapon",
 * })
 */
abstract class EquipmentMechanic
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    protected array $mechanics = [];

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Action\Entity\Action")
     */
    private Collection $actions;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
    }

    public function initEquipment(GameEquipment $gameEquipment): GameEquipment
    {
        return $gameEquipment;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMechanics(): array
    {
        return $this->mechanics;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    /**
     * @return static
     */
    public function setActions(Collection $actions): self
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * @return static
     */
    public function addAction(Action $action): self
    {
        $this->actions->add($action);

        return $this;
    }
}
