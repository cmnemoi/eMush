<?php

namespace Mush\Equipment\Entity\Config\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Drug extends Ration
{
    protected string $mechanic = EquipmentMechanicEnum::DRUG;

    protected bool $isPerishable = false;
}
