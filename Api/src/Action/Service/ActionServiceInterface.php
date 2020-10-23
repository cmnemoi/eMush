<?php

namespace Mush\Action\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Player\Entity\Player;

interface ActionServiceInterface
{
    public function executeAction(Player $player, string $actionName, array $params): ActionResult;
}
