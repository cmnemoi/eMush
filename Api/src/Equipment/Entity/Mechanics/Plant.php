<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\ProbaCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Plant extends EquipmentMechanic
{
    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    private string $fruitName;

    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    private array $maturationTime;

    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    private array $oxygen;

    public function __construct()
    {
        parent::__construct();
        $this->maturationTime = [];
        $this->oxygen = [];
    }

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::PLANT;

        return $mechanics;
    }

    public function getFruitName(): string
    {
        return $this->fruitName;
    }

    public function setFruitName(string $fruitName)
    {
        $this->fruitName = $fruitName;

        return $this;
    }

    public function getMaturationTime(): ProbaCollection
    {
        return new ProbaCollection($this->maturationTime);
    }

    public function setMaturationTime(array $maturationTime): static
    {
        $this->maturationTime = $maturationTime;

        return $this;
    }

    public function getOxygen(): ProbaCollection
    {
        return new ProbaCollection($this->oxygen);
    }

    public function setOxygen(array $oxygen): static
    {
        $this->oxygen = $oxygen;

        return $this;
    }
}
