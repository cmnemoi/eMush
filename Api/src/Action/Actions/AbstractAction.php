<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Event\ActionEvent;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAction
{
    protected Action $action;
    protected Player $player;

    protected string $name;

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        $this->action = $action;
        $this->player = $player;
    }

    abstract public function canExecute(): bool;

    abstract protected function applyEffects(): ActionResult;

    public function execute(): ActionResult
    {
        if (!$this->canExecute() ||
            !$this->action->getActionCost()->canPlayerDoAction($this->player) ||
            !$this->player->isAlive()) {
            return new Error('Cannot execute action');
        }

        $preActionEvent = new ActionEvent($this->action, $this->player);
        $this->eventDispatcher->dispatch($preActionEvent, ActionEvent::PRE_ACTION);

        $this->applyActionCost();
        $result = $this->applyEffects();

        $postActionEvent = new ActionEvent($this->action, $this->player);
        $postActionEvent->setActionResult($result);
        $this->eventDispatcher->dispatch($postActionEvent, ActionEvent::POST_ACTION);

        return $result;
    }

    protected function applyActionCost(): Player
    {
        $this->action->getActionCost()->applyCostToPlayer($this->player);

        return $this->player;
    }

    public function getActionName(): string
    {
        return $this->name;
    }
}
