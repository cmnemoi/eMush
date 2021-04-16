<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ActionService implements ActionServiceInterface
{
    public const MAX_PERCENT = 99;

    private ActionModifierServiceInterface $actionModifierService;
    private StatusServiceInterface $statusService;

    public function __construct(
        ActionModifierServiceInterface $actionModifierService,
        StatusServiceInterface $statusService
    ) {
        $this->actionModifierService = $actionModifierService;
        $this->statusService = $statusService;
    }

    public function canPlayerDoAction(Player $player, Action $action): bool
    {
        return $this->getTotalActionPointCost($player, $action) <= $player->getActionPoint() &&
            ($this->getTotalMovementPointCost($player, $action) <= $player->getMovementPoint() || $player->getActionPoint() > 0) &&
            $this->getTotalMoralPointCost($player, $action) <= $player->getMoralPoint()
        ;
    }

    public function applyCostToPlayer(Player $player, Action $action): Player
    {
        return $player
            ->addActionPoint((-1) * $this->getTotalActionPointCost($player, $action))
            ->addMovementPoint((-1) * $this->getTotalMovementPointCost($player, $action))
            ->addMoralPoint((-1) * $this->getTotalMoralPointCost($player, $action))
            ;
    }

    public function getTotalActionPointCost(Player $player, Action $action): int
    {
        $initCost = $action->getActionCost()->getActionPointCost();

        if ($initCost !== null) {
            $actionCost = $this->actionModifierService->getModifiedValue(
                $initCost,
                $player,
                array_merge([$action->getName()], $action->getTypes()),
                ModifierTargetEnum::ACTION_POINT
            );

            return max($actionCost, 0);
        }

        return 0;
    }

    public function getTotalMovementPointCost(Player $player, Action $action): int
    {
        $initCost = $action->getActionCost()->getMovementPointCost();

        if ($initCost !== null) {
            $actionCost = $this->actionModifierService->getModifiedValue(
                $initCost,
                $player,
                array_merge([$action->getName()], $action->getTypes()),
                ModifierTargetEnum::MOVEMENT_POINT
            );

            return max($actionCost, 0);
        }

        return 0;
    }

    public function getTotalMoralPointCost(Player $player, Action $action): int
    {
        return $action->getActionCost()->getMoralPointCost() ?? 0;
    }

    public function getSuccessRate(
        Action $action,
        Player $player
    ): int {
        $baseRate = $action->getSuccessRate();

        //Get number of attempt
        $numberOfAttempt = $this->getAttempt($player, $action->getName())->getCharge();

        $initialValue = ($baseRate * (1.25) ** $numberOfAttempt);

        //Get modifiers
        $modifiedValue = $this->actionModifierService->getModifiedValue(
            $initialValue,
            $player,
            array_merge([$action->getName()], $action->getTypes()),
            ModifierTargetEnum::PERCENTAGE
        );

        return min($this::MAX_PERCENT, $modifiedValue);
    }

    public function getAttempt(Player $player, string $actionName): Attempt
    {
        /** @var Attempt $attempt */
        $attempt = $player->getStatusByName(StatusEnum::ATTEMPT);

        if ($attempt && $attempt->getAction() !== $actionName) {
            // Re-initialize attempts with new action
            $attempt
                ->setAction($actionName)
                ->setCharge(0)
            ;
        } elseif ($attempt === null) { //Create Attempt
            $attempt = $this->statusService->createAttemptStatus(
                StatusEnum::ATTEMPT,
                $actionName,
                $player
            );
        }

        return $attempt;
    }
}
