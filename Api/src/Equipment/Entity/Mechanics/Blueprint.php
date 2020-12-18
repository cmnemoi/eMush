<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Blueprint extends Tool
{
    protected string $mechanic = EquipmentMechanicEnum::BLUEPRINT;

    protected array $actions = [ActionEnum::BUILD];

    /**
     * @ORM\OneToOne(targetEntity="Mush\Equipment\Entity\EquipmentConfig", inversedBy=")
     */
    private ?EquipmentConfig $equipment = null;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private ?array $ingredients = null;

    public function getEquipment(): ?EquipmentConfig
    {
        return $this->equipment;
    }

    /**
     * @return static
     */
    public function setEquipment(EquipmentConfig $equipment): Blueprint
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getIngredients(): ?array
    {
        return $this->ingredients;
    }

    /**
     * @return static
     */
    public function setIngredients(array $ingredients): Blueprint
    {
        $this->ingredients = $ingredients;

        return $this;
    }
}
