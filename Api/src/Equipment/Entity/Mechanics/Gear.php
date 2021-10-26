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
    /**
     * @ORM\ManyToMany(targetEntity="Mush\Modifier\Entity\ModifierConfig")
     */
    private Collection $modifierConfigs;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::GEAR;

        return $mechanics;
    }

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    /**
     * @return static
     */
    public function setModifierConfigs(Collection $modifierConfigs): self
    {
        $this->modifierConfigs = $modifierConfigs;

        return $this;
    }
}
