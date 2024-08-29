<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class Fruit extends Ration
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $plantName;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::FRUIT;

        return $mechanics;
    }

    public function getPlantName(): string
    {
        return $this->plantName;
    }

    public function setPlantName(string $plantName): self
    {
        $this->plantName = $plantName;

        return $this;
    }
}
