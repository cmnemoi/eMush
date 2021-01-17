<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;

interface ActionModifierServiceInterface
{
    public function handlePlayerModifier(Player $player, Modifier $actionModifier, \DateTime $date = null): Player;
}
