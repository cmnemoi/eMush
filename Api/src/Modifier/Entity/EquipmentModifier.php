<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\GameEquipment;

/**
 * Class EquipmentModifier.
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

        $gameEquipment->addModifier($this);

        return $this;
    }
}
