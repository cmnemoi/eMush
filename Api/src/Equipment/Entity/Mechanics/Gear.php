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

    /**
     * Gear constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::GEAR;
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
