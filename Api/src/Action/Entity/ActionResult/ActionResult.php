<?php

namespace Mush\Action\ActionResult;

use Mush\Equipment\Entity\Equipment;

abstract class ActionResult
{
    protected const DEFAULT = 'default';

    private ?Equipment $equipment = null;
    private ?int $quantity = null;

    public function setEquipment(Equipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getName(): string
    {
        return self::DEFAULT;
    }
}
