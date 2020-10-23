<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Entity\ActionParameters;
use Mush\Player\Entity\Player;

abstract class Action
{
    abstract public function loadParameters(Player $player, ActionParameters $actionParameters);
    abstract protected function apply(): ActionResult;
    abstract protected function createLog(ActionResult $actionResult): void;

    public function execute(): ActionResult
    {
        if (!$this->canExecute()) {
            return new Error('Cannot execute action');
        }

        $result = $this->apply();
        $this->createLog($result);

        return $result;
    }
}
