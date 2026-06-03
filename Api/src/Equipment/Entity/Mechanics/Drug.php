<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class Drug extends Ration
{
    public function __construct()
    {
        parent::__construct();
        $this->isPerishable = false;
    }

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::DRUG;

        return $mechanics;
    }
}
