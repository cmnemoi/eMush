<?php

namespace Mush\Equipment\Entity\Config\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Entity extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::ENTITY;
}
