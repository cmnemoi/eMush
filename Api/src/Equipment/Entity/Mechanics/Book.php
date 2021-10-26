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

    /**
     * Book constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::BOOK;
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
