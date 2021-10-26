<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Exploration extends Gear
{
    /**
     * Exploration constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::EXPLORATION;
    }
}
