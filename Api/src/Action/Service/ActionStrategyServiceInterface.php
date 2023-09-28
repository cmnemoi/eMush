<?php

namespace Mush\Action\Service;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Player\Entity\Player;

interface ActionStrategyServiceInterface
{
    public function getAction(string $actionName): ?AbstractAction;

    public function executeAction(Player $player, int $actionId, array $params): ActionResult;
}
