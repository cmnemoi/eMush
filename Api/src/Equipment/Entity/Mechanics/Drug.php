<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Drug extends Ration
{
    protected bool $isPerishable = false;

    /**
     * Drug constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::DRUG;
    }
}
