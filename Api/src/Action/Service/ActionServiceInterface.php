<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;

interface ActionServiceInterface
{
    public function handleActionEffect(Action $action, Player $player, ?\DateTime $date): Player;
}
