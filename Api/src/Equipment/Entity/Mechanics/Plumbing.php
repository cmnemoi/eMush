<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\Collection\ProbaCollection;

// @TODO: Deprecated. Do not use. Convert all Plumbing items into tools, then delete me.
#[ORM\Entity]
class Plumbing extends Tool
{
    /**
     * Possibilities are stored as key, array value represent the probability to get the key value.
     *
     * @see ProbaCollection
     */
    #[ORM\Column(type: 'array', nullable: false)]
    private array $waterDamage;

    public function __construct()
    {
        parent::__construct();
    }

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::PLUMBING;

        return $mechanics;
    }

    public function getWaterDamage(): ProbaCollection
    {
        return new ProbaCollection($this->waterDamage);
    }

    public function setWaterDamage(array $waterDamage): static
    {
        $this->waterDamage = $waterDamage;

        return $this;
    }
}
