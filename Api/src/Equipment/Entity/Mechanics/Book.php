<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Skill\Enum\SkillEnum;

#[ORM\Entity]
class Book extends Tool
{
    #[ORM\Column(type: 'string', nullable: false, enumType: SkillEnum::class, options: ['default' => SkillEnum::NULL])]
    private SkillEnum $skill = SkillEnum::NULL;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::BOOK;

        return $mechanics;
    }

    public function getSkill(): SkillEnum
    {
        return $this->skill;
    }

    /**
     * @return static
     */
    public function setSkill(SkillEnum $skill): self
    {
        $this->skill = $skill;

        return $this;
    }

    public function isMageBook(): bool
    {
        return $this->skill !== SkillEnum::NULL;
    }
}
