<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Blueprint extends Tool
{
    /**
     * @ORM\OneToOne(targetEntity="Mush\Equipment\Entity\Config\EquipmentConfig")
     */
    private EquipmentConfig $equipment;

    /**
     * @ORM\Column(type="array", nullable=false)
     *
     * @var array<string, int>
     */
    private array $ingredients = [];

    /**
     * BluePrint constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::BLUEPRINT;
    }

    public function getEquipment(): EquipmentConfig
    {
        return $this->equipment;
    }

    /**
     * @return static
     */
    public function setEquipment(EquipmentConfig $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    /**
     * @return static
     */
    public function setIngredients(array $ingredients): self
    {
        $this->ingredients = $ingredients;

        return $this;
    }
}
