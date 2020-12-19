<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\EquipmentConfig;
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

    protected array $actions = [ActionEnum::WATER_PLANT, ActionEnum::TREAT_PLANT, ActionEnum::HYBRIDIZE];

    /**
     * @ORM\OneToOne(targetEntity="Mush\Equipment\Entity\EquipmentConfig", inversedBy=")
     */
    private ?EquipmentConfig $fruit = null;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $maturationTime = [];

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $minOxygen;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maxOxygen;

    public function getFruit(): ?EquipmentConfig
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

    public function getMinOxygen(): int
    {
        return $this->minOxygen;
    }

    /**
     * @return static
     */
    public function setMinOxygen(int $minOxygen): Plant
    {
        $this->minOxygen = $minOxygen;

        return $this;
    }

    public function getMaxOxygen(): int
    {
        return $this->maxOxygen;
    }

    /**
     * @return static
     */
    public function setMaxOxygen(int $maxOxygen): Plant
    {
        $this->maxOxygen = $maxOxygen;

        return $this;
    }
}
