<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity()
 */
class Tool extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::TOOL;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $grantActions = [];

    public function getGrantActions(): array
    {
        return $this->grantActions;
    }

    public function setGrantActions(array $grantActions): Tool
    {
        $this->grantActions = $grantActions;

        return $this;
    }

}
