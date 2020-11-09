<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionCost;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Entity\ActionParameters;
use Mush\Player\Entity\Player;

abstract class Action
{
    protected ActionCost $actionCost;
    protected Player $player;

    abstract public function loadParameters(Player $player, ActionParameters $actionParameters);
    abstract public function canExecute(): bool;
    abstract public function getActionName(): string;

    abstract protected function applyEffects(): ActionResult;
    abstract protected function createLog(ActionResult $actionResult): void;

    public function execute(): ActionResult
    {
        if (!$this->canExecute() || !$this->getActionCost()->canPlayerDoAction($this->player)) {
            return new Error('Cannot execute action');
        }

        $this->applyActionCost();
        $result = $this->applyEffects();
        $this->createLog($result);

        return $result;
    }

    protected function applyActionCost(): Player
    {
        $this->actionCost->applyCostToPlayer($this->player);

        return $this->player;
    }

    public function getActionCost(): ActionCost
    {
        $this->actionCost = $this->player->getMedicalConditions()->applyActionCostModificator($this->actionCost);
        return $this->actionCost;
    }
}
