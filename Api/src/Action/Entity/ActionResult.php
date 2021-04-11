<?php

namespace Mush\Action\ActionResult;

use Mush\Action\Entity\ActionParameter;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

abstract class ActionResult
{
    private ?Player $targetPlayer = null;
    private ?GameEquipment $targetEquipment = null;

    public function __construct(ActionParameter $actionParameter = null)
    {
        if ($actionParameter !== null) {
            $this->setActionParameter($actionParameter);
        }
    }

    public function setActionParameter(ActionParameter $actionParameter): static
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
}
