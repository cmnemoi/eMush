<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class Blueprint extends Tool
{
    #[ORM\ManyToOne(targetEntity: EquipmentConfig::class)]
    private EquipmentConfig $equipment;

    /**
     * @var array<string, int>
     */
    #[ORM\Column(type: 'array', nullable: false)]
    private array $ingredients = [];

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::BLUEPRINT;

        return $mechanics;
    }

    public function getEquipment(): EquipmentConfig
    {
        return $this->equipment;
    }

    public function setEquipment(EquipmentConfig $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    public function setIngredients(array $ingredients): static
    {
        $this->ingredients = $ingredients;

        return $this;
    }
}
