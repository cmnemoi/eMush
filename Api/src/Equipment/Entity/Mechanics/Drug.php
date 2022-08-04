<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class Drug extends Ration
{
    protected bool $isPerishable = false;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::DRUG;

        return $mechanics;
    }
}
