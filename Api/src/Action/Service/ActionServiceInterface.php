<?php

namespace Mush\Action\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Player\Entity\Player;

interface ActionServiceInterface
{
    public function getAction(string $actionName): ?Action;

    public function canExecuteAction(Player $player, string $actionName, ActionParameters $params): bool;

    public function executeAction(Player $player, string $actionName, array $params): ActionResult;
}
