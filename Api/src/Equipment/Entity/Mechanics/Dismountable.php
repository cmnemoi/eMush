<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Dismountable extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::DISMOUNTABLE;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $chancesSuccess = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $actionCost = 0;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $products = [];

    public function getChancesSuccess(): int
    {
        return $this->chancesSuccess;
    }

    /**
     * @return static
     */
    public function setChancesSuccess(int $chancesSuccess): Dismountable
    {
        $this->chancesSuccess = $chancesSuccess;

        return $this;
    }

    public function getActionCost(): int
    {
        return $this->actionCost;
    }

    /**
     * @return static
     */
    public function setActionCost(int $actionCost): Dismountable
    {
        $this->actionCost = $actionCost;

        return $this;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @return static
     */
    public function setProducts(array $products): Dismountable
    {
        $this->products = $products;

        return $this;
    }
}
