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
     * @ORM\ManyToMany(targetEntity="Mush\Modifier\Entity\ModifierConfig")
     */
    private Collection $modifierConfigs;

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    /**
     * @return static
     */
    public function setModifierConfigs(Collection $modifierConfigs): Gear
    {
        $this->modifierConfigs = $modifierConfigs;

        return $this;
    }
}
