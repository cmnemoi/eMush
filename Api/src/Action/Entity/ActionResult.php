<?php

namespace Mush\Action\ActionResult;

use Mush\Action\Entity\ActionParameter;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

abstract class ActionResult
{
    private ?GameEquipment $equipment = null;
    private ?int $quantity = null;

    public function setEquipment(GameEquipment $equipment): self
    {
        $this->equipment = $equipment;
        return $this;
    }

    public function getEquipment(): ?GameEquipment
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
}
