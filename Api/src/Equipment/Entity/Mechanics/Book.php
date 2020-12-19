<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Book extends Tool
{
    protected string $mechanic = EquipmentMechanicEnum::BOOK;

    protected array $actions = [ActionEnum::READ_BOOK];

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $skill;

    public function getSkill(): string
    {
        return $this->skill;
    }

    /**
     * @return static
     */
    public function setSkill(string $skill): Book
    {
        $this->skill = $skill;

        return $this;
    }
}
