<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class Blueprint extends Tool
{
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $craftedEquipmentName;

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

    public function getCraftedEquipmentName(): string
    {
        return $this->craftedEquipmentName;
    }

    public function setCraftedEquipmentName(string $craftedEquipmentName): self
    {
        $this->craftedEquipmentName = $craftedEquipmentName;

        return $this;
    }

    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    public function setIngredients(array $ingredients): self
    {
        $this->ingredients = $ingredients;

        return $this;
    }
}
