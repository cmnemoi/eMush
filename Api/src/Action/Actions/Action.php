<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Event\ActionEvent;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class Action
{
    protected ActionCost $actionCost;
    protected Player $player;

    protected string $name;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        $this->actionCost = new ActionCost();
    }

    abstract public function loadParameters(Player $player, ActionParameters $actionParameters): void;

    abstract public function canExecute(): bool;

    abstract protected function applyEffects(): ActionResult;

    abstract protected function createLog(ActionResult $actionResult): void;

    public function execute(): ActionResult
    {
        if (!$this->canExecute() || !$this->getActionCost()->canPlayerDoAction($this->player)) {
            return new Error('Cannot execute action');
        }

        $preActionEvent = new ActionEvent($this->getActionName(), $this->player, $this->actionCost);
        $this->eventDispatcher->dispatch($preActionEvent, ActionEvent::PRE_ACTION);

        $this->applyActionCost();
        $result = $this->applyEffects();
        $this->createLog($result);

        $postActionEvent = new ActionEvent($this->getActionName(), $this->player, $this->actionCost);
        $this->eventDispatcher->dispatch($postActionEvent, ActionEvent::POST_ACTION);

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

    public function getActionName(): string
    {
        return $this->name;
    }
}
