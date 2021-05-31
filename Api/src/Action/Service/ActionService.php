<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActionService implements ActionServiceInterface
{
    public const MAX_PERCENT = 99;
    public const BASE_MOVEMENT_POINT_CONVERSION = 3;

    private EventDispatcherInterface $eventDispatcher;
    private ActionModifierServiceInterface $actionModifierService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionModifierServiceInterface $actionModifierService,
        StatusServiceInterface $statusService
    ) {
        $this->eventDispatcher = $eventDispatcher;
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
        if (($actionPointCost = $this->getTotalActionPointCost($player, $action)) > 0) {
            $this->triggerPlayerModifierEvent($player, PlayerModifierEvent::ACTION_POINT_MODIFIER, -$actionPointCost);
        }

        if (($movementPointCost = $this->getTotalMovementPointCost($player, $action)) > 0) {
            if ($player->getMovementPoint() === 0) {
                $playerModifierEvent = new PlayerModifierEvent($player, self::BASE_MOVEMENT_POINT_CONVERSION, new \DateTime());
                $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MOVEMENT_POINT_CONVERSION);
            }

            $this->triggerPlayerModifierEvent($player, PlayerModifierEvent::MOVEMENT_POINT_MODIFIER, -$movementPointCost);
        }

        if (($moralPointCost = $this->getTotalMoralPointCost($player, $action)) > 0) {
            $this->triggerPlayerModifierEvent($player, PlayerModifierEvent::MORAL_POINT_MODIFIER, -$moralPointCost);
        }

        return $player;
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
        $numberOfAttempt = $this->getNumberOfAttempt($player, $action->getName());

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

    private function getNumberOfAttempt(Player $player, string $actionName): int
    {
        /** @var Attempt $attempt */
        $attempt = $player->getStatusByName(StatusEnum::ATTEMPT);

        if ($attempt && $attempt->getAction() === $actionName) {
            return $attempt->getCharge();
        }

        return 0;
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

    private function triggerPlayerModifierEvent(Player $player, string $eventName, int $delta): void
    {
        $playerModifierEvent = new PlayerModifierEvent($player, $delta, new \DateTime());
        $playerModifierEvent->setIsDisplayedRoomLog(false);
        $this->eventDispatcher->dispatch($playerModifierEvent, $eventName);
    }
}
