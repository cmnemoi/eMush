<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class PatrolShip extends EquipmentMechanic
{   
    #[ORM\Column(type: 'array', nullable: false, default: [])]
    private array $collectScrapNumber;
    
    #[ORM\Column(type: 'array', nullable: false, default: [])]
    private array $collectScrapPilotDamage;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::PATROL_SHIP;

        return $mechanics;
    }

}
