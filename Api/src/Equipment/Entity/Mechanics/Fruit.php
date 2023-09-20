<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Fruit extends Ration
{
    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
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

    public function setPlantName(string $plantName): static
    {
        $this->plantName = $plantName;

        return $this;
    }
}
