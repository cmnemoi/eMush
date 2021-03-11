<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;

interface PlayerVariableServiceInterface
{
    public function modifyPlayerVariable(Player $player, Modifier $actionModifier, \DateTime $date = null): Player;
}
