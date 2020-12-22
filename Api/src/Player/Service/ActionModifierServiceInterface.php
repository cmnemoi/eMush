<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;

interface ActionModifierServiceInterface
{
    public function handlePlayerModifier(Player $player, ActionModifier $actionModifier, \DateTime $date = null): Player;
}
