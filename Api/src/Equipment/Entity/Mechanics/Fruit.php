<?php

namespace Mush\Equipment\Entity\Config\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Fruit extends Ration
{
    protected string $mechanic = EquipmentMechanicEnum::FRUIT;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $plantName;

    public function getPlantName(): string
    {
        return $this->plantName;
    }

    /**
     * @return static
     */
    public function setPlantName(string $plantName): Fruit
    {
        $this->plantName = $plantName;

        return $this;
    }
}
