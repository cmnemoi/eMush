<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Modifier\Entity\Config\ModifierConfig;

#[ORM\Entity]
class Gear extends EquipmentMechanic
{
    #[ORM\ManyToMany(targetEntity: ModifierConfig::class)]
    private Collection $modifierConfigs;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::GEAR;

        return $mechanics;
    }

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    public function setModifierConfigs(Collection $modifierConfigs): static
    {
        $this->modifierConfigs = $modifierConfigs;

        return $this;
    }
}
