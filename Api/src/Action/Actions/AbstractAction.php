<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Service\ActionModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAction
{
    protected Action $action;
    protected Player $player;

    protected string $name;

    protected EventDispatcherInterface $eventDispatcher;
    protected GearToolServiceInterface $gearToolService;
    protected ActionModifierServiceInterface $actionModifierService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GearToolServiceInterface $gearToolService,
        ActionModifierServiceInterface $actionModifierService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->gearToolService = $gearToolService;
        $this->actionModifierService = $actionModifierService;
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
        $this->eventDispatcher->dispatch($postActionEvent, ActionEvent::RESULT_ACTION);

        $postActionEvent = new ActionEvent($this->action, $this->player);
        $postActionEvent->setActionResult($result);
        $this->eventDispatcher->dispatch($postActionEvent, ActionEvent::POST_ACTION);

        return $result;
    }

    protected function applyActionCost(): Player
    {
        $this->getActionCost()->applyCostToPlayer($this->player);

        $gears = $this->gearToolService->getApplicableGears(
            $this->player,
            array_merge([$this->getActionName()], $this->action->getTypes()),
            [ReachEnum::INVENTORY],
            ModifierTargetEnum::ACTION_POINT
        );

        foreach ($gears as $gear) {
            $this->gearToolService->applyChargeCost($gear);
        }

        $tool = $this->gearToolService->getUsedTool($this->player, $this->action->getName());
        if ($tool) {
            $this->gearToolService->applyChargeCost($tool);
        }

        return $this->player;
    }

    public function getActionCost(): ActionCost
    {
        $actionCost = $this->action->getActionCost();

        $modifiers = $this->actionModifierService->getActionModifier(
            $this->player,
            array_merge([$this->getActionName()], $this->action->getTypes()),
            [ReachEnum::INVENTORY],
            ModifierTargetEnum::ACTION_POINT
        );

        /** @var Modifier $modifier */
        foreach ($modifiers as $modifier) {
            if ($actionCost->getActionPointCost() > 0 &&
               $modifier->getTarget() === ModifierTargetEnum::ACTION_POINT
           ) {
                $actionCost->addActionPointCost((int) $modifier->getDelta());
            }
        }

        return $actionCost;
    }

    public function getActionName(): string
    {
        return $this->name;
    }
}
