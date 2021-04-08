<?php

namespace Mush\Action\ActionResult;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

abstract class ActionResult
{
    private ?Player $targetPlayer;
    private ?GameEquipment $targetEquipment;

    public function __construct(?Player $targetPlayer = null, ?GameEquipment $targetEquipment = null)
    {
        $this->targetPlayer = $targetPlayer;
        $this->targetEquipment = $targetEquipment;
    }

    public function setTargetPlayer(?Player $targetPlayer): ActionResult
    {
        $this->targetPlayer = $targetPlayer;

        return $this;
    }

    public function getTargetPlayer(): ?Player
    {
        return $this->targetPlayer;
    }

    public function setTargetEquipment(?GameEquipment $targetEquipment): ActionResult
    {
        $this->targetEquipment = $targetEquipment;

        return $this;
    }

    public function getTargetEquipment(): ?GameEquipment
    {
        return $this->targetEquipment;
    }
}
