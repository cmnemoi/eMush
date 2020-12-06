<?php

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class EquipmentMechanic.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "blue_print" = "Mush\Equipment\Entity\Mechanics\Blueprint",
 *     "book" = "Mush\Equipment\Entity\Mechanics\Book",
 *     "document" = "Mush\Equipment\Entity\Mechanics\Document",
 *     "drug" = "Mush\Equipment\Entity\Mechanics\Drug",
 *     "entity" = "Mush\Equipment\Entity\Mechanics\Entity",
 *     "exploration" = "Mush\Equipment\Entity\Mechanics\Exploration",
 *     "fruit" = "Mush\Equipment\Entity\Mechanics\Fruit",
 *     "gear" = "Mush\Equipment\Entity\Mechanics\Gear",
 *     "instrument" = "Mush\Equipment\Entity\Mechanics\Instrument",
 *     "plant" = "Mush\Equipment\Entity\Mechanics\Plant",
 *     "ration" = "Mush\Equipment\Entity\Mechanics\Ration",
 *     "tool" = "Mush\Equipment\Entity\Mechanics\Tool",
 *     "weapon" = "Mush\Equipment\Entity\Mechanics\Weapon",
 *     "dismountable" = "Mush\Equipment\Entity\Mechanics\Dismountable",
 *     "charged" = "Mush\Equipment\Entity\Mechanics\Charged"
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

    protected string $mechanic;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    protected array $actions = [];

    public function initEquipment(GameEquipment $gameEquipment): GameEquipment
    {
        return $gameEquipment;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMechanic(): string
    {
        return $this->mechanic;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): EquipmentMechanic
    {
        $this->actions = $actions;

        return $this;
    }

    public function addAction(string $action): EquipmentMechanic
    {
        $this->getActions->add($action);

        return $this;
    }
}
