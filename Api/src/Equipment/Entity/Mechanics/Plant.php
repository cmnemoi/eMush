<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\ProbaCollection;

#[ORM\Entity]
class Plant extends EquipmentMechanic
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $fruitName;

    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $maturationTime;

    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $oxygen;

    public function __construct()
    {
        parent::__construct();
        $this->maturationTime = new ProbaCollection();
        $this->oxygen = new ProbaCollection();
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
        return $this->maturationTime;
    }

    public function setMaturationTime(array $maturationTime): static
    {
        $this->maturationTime = new ProbaCollection($maturationTime);

        return $this;
    }

    public function getOxygen(): ProbaCollection
    {
        return $this->oxygen;
    }

    public function setOxygen(array $oxygen): static
    {
        $this->oxygen = new ProbaCollection($oxygen);

        return $this;
    }
}
