<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Gear extends EquipmentMechanic
{
    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
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

    /**
     * @param array<int, AbstractModifierConfig>|Collection<int, AbstractModifierConfig> $modifierConfigs
     */
    public function setModifierConfigs(Collection|array $modifierConfigs): static
    {
        if (is_array($modifierConfigs)) {
            $modifierConfigs = new ArrayCollection($modifierConfigs);
        }

        $this->modifierConfigs = $modifierConfigs;

        return $this;
    }
}
