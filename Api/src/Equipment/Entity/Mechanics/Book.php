<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Book extends Tool
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $skill;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::BOOK;

        return $mechanics;
    }

    public function getSkill(): string
    {
        return $this->skill;
    }

    /**
     * @return static
     */
    public function setSkill(string $skill): self
    {
        $this->skill = $skill;

        return $this;
    }
}
