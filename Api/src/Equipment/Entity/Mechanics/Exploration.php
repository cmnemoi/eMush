<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class Exploration extends Gear
{
    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::EXPLORATION;

        return $mechanics;
    }
}
