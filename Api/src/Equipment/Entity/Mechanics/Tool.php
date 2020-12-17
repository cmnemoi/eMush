<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity()
 */
class Tool extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::TOOL;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $grantActions = [];
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $actionsTarget = [];

    public function getGrantActions(): Collection
    {
        return new ArrayCollection($this->grantActions);
    }

    public function setGrantActions(array $grantActions): Tool
    {
        $this->grantActions = $grantActions;

        return $this;
    }

    public function getActionsTarget(): array
    {
        return $this->actionsTarget;
    }

    public function setActionsTarget(array $actionsTarget): Tool
    {
        $this->actionsTarget = $actionsTarget;

        return $this;
    }

    //@TODO maybe create a reach property
}
