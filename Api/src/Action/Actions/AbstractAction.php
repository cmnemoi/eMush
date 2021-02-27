<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAction
{
    protected Action $action;
    protected Player $player;

    protected $parameter = null;

    protected string $name;

    protected EventDispatcherInterface $eventDispatcher;
    protected ActionServiceInterface $actionService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->actionService = $actionService;
    }

    abstract protected function support(?ActionParameter $parameter): bool;

    public function loadParameters(Action $action, Player $player, ?ActionParameter $parameter = null): void
    {
        if (!$this->support($parameter)) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->action = $action;
        $this->player = $player;
        $this->parameter = $parameter;
    }

    public function isVisible(): bool
    {
        return $this->player->isAlive();
    }

    public function cannotExecuteReason(): ?string
    {
        if (!$this->actionService->canPlayerDoAction($this->player, $this->action)) {
            return ActionImpossibleCauseEnum::INSUFFICIENT_ACTION_POINT;
        }

        return null;
    }

    abstract protected function applyEffects(): ActionResult;

    public function execute(): ActionResult
    {
        if (!$this->isVisible() ||
            $this->cannotExecuteReason() !== null
        ) {
            return new Error('Cannot execute action');
        }

        $preActionEvent = new ActionEvent($this->action, $this->player);
        $this->eventDispatcher->dispatch($preActionEvent, ActionEvent::PRE_ACTION);

        $this->actionService->applyCostToPlayer($this->player, $this->action);

        $result = $this->applyEffects();

        $postActionEvent = new ActionEvent($this->action, $this->player);
        $postActionEvent->setActionResult($result);
        $this->eventDispatcher->dispatch($postActionEvent, ActionEvent::RESULT_ACTION);

        $postActionEvent = new ActionEvent($this->action, $this->player);
        $postActionEvent->setActionResult($result);
        $this->eventDispatcher->dispatch($postActionEvent, ActionEvent::POST_ACTION);

        return $result;
    }

    public function getActionName(): string
    {
        return $this->name;
    }

    public function getActionPointCost(): ?int
    {
        return $this->actionService->getTotalActionPointCost($this->player, $this->action);
    }

    public function getMovementPointCost(): ?int
    {
        return $this->actionService->getTotalMovementPointCost($this->player, $this->action);
    }

    public function getMoralPointCost(): ?int
    {
        return $this->actionService->getTotalMoralPointCost($this->player, $this->action);
    }
}
