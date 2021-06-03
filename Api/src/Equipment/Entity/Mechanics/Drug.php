<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Drug extends Ration
{
    protected string $mechanic = EquipmentMechanicEnum::DRUG;

    protected bool $isPerishable = false;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $drugEffectsNumber = [];

    //@TODO more precision on the cure is needed (is the number of disease point remooved random)

    public function getDrugEffectsNumber(): array
    {
        return $this->drugEffectsNumber;
    }

    /**
     * @return static
     */
    public function setDrugEffectsNumber(array $drugEffectsNumber): Drug
    {
        $this->drugEffectsNumber = $drugEffectsNumber;

        return $this;
    }
}
