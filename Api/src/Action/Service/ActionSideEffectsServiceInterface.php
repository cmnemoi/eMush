<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;

interface ActionSideEffectsServiceInterface
{
    public function handleActionSideEffect(Action $action, Player $player, ?\DateTime $date): Player;
}
