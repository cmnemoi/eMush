<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Gear extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::GEAR;

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Modifier\Entity\Modifier")
     */
    private Collection $modifiers;

    public function getModifiers(): Collection
    {
        return $this->modifiers;
    }

    /**
     * @return static
     */
    public function setModifier(Collection $modifiers): Gear
    {
        $this->modifiers = $modifiers;

        return $this;
    }
}
