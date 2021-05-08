<?php

namespace Mush\Action\ActionResult;

use Mush\Action\Entity\ActionParameter;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

abstract class ActionResult
{
    private ?Player $targetPlayer = null;
    private ?GameEquipment $targetEquipment = null;
    private ?int $quantity = null;

    public function __construct(ActionParameter $actionParameter = null, $quantity = null)
    {
        if ($actionParameter !== null) {
            $this->setActionParameter($actionParameter);
        }

        $this->quantity = $quantity;
    }

    public function setActionParameter(ActionParameter $actionParameter): self
    {
        if ($actionParameter instanceof Player) {
            if ($this->targetEquipment !== null) {
                throw new \Exception('Action result should be either Player or GameEquipment');
            }

            $this->targetPlayer = $actionParameter;
        }
        if ($actionParameter instanceof GameEquipment) {
            if ($this->targetEquipment !== null) {
                throw new \Exception('Action result should be either Player or GameEquipment');
            }

            $this->targetEquipment = $actionParameter;
        }

        return $this;
    }

    public function getTargetPlayer(): ?Player
    {
        return $this->targetPlayer;
    }

    public function getTargetEquipment(): ?GameEquipment
    {
        return $this->targetEquipment;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }
}
