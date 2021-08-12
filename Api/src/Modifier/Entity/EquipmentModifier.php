<?php

namespace Mush\Modifier\Entity;

use Mush\Equipment\Entity\GameEquipment;

/**
 * Class Modifier.
 *
 * @ORM\Entity
 */
class EquipmentModifier extends Modifier
{
    /**
     * @ORM\ManyToOne (targetEntity="Mush\Equipment\Entity\GameEquipment", inversedBy="modifiers")
     */
    private GameEquipment $gameEquipment;

    public function getEquipment(): GameEquipment
    {
        return $this->gameEquipment;
    }

    public function setEquipment(GameEquipment $gameEquipment): EquipmentModifier
    {
        $this->gameEquipment = $gameEquipment;

        return $this;
    }
}
