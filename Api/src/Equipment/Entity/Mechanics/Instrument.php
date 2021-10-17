<?php

namespace Mush\Equipment\Entity\Config\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Instrument extends Gear
{
    protected string $mechanic = EquipmentMechanicEnum::INSTRUMENT;
}
