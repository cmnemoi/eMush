<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Player\Entity\Modifier;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Gear extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::GEAR;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Modifier")
     */
    private Modifier $modifier;

    public function getModifier(): Modifier
    {
        return $this->modifier;
    }

    /**
     * @return static
     */
    public function setModifier(Modifier $modifier): Gear
    {
        $this->modifier = $modifier;

        return $this;
    }
}
