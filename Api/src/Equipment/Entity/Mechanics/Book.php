<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Skill\Enum\SkillName;

#[ORM\Entity]
class Book extends Tool
{
    #[ORM\Column(type: 'string', enumType: SkillName::class, nullable: false, options: ['default' => SkillName::NULL])]
    private SkillName $skill = SkillName::NULL;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::BOOK;

        return $mechanics;
    }

    public function getSkill(): SkillName
    {
        return $this->skill;
    }

    /**
     * @return static
     */
    public function setSkill(SkillName $skill): self
    {
        $this->skill = $skill;

        return $this;
    }
}
