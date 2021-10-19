<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity()
 */
class Plant extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::PLANT;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Equipment\Entity\Config\EquipmentConfig", inversedBy=")
     */
    private EquipmentConfig $fruit;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $maturationTime = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $oxygen;

    public function getFruit(): EquipmentConfig
    {
        return $this->fruit;
    }

    /**
     * @return static
     */
    public function setFruit(EquipmentConfig $fruit): Plant
    {
        $this->fruit = $fruit;

        return $this;
    }

    public function getMaturationTime(): array
    {
        return $this->maturationTime;
    }

    /**
     * @return static
     */
    public function setMaturationTime(array $maturationTime): Plant
    {
        $this->maturationTime = $maturationTime;

        return $this;
    }

    public function getOxygen(): array
    {
        return $this->oxygen;
    }

    /**
     * @return static
     */
    public function setOxygen(array $oxygen): Plant
    {
        $this->oxygen = $oxygen;

        return $this;
    }
}
