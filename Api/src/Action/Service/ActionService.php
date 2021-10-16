<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerModifierEventInterface;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActionService implements ActionServiceInterface
{
    public const MAX_PERCENT = 99;
    public const BASE_MOVEMENT_POINT_CONVERSION = 3;

    private EventDispatcherInterface $eventDispatcher;
    private ActionModifierServiceInterface $actionModifierService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionModifierServiceInterface $actionModifierService,
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->actionModifierService = $actionModifierService;
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
            $this->triggerPlayerModifierEvent($player, PlayerModifierEventInterface::ACTION_POINT_MODIFIER, -$actionPointCost);
        }

        if (($movementPointCost = $this->getTotalMovementPointCost($player, $action)) > 0) {
            if ($player->getMovementPoint() === 0) {
                $playerModifierEvent = new PlayerModifierEventInterface(
                    $player,
                    self::BASE_MOVEMENT_POINT_CONVERSION,
                    'movement point conversion', //@TODO incoming with modifier merge request
                    new \DateTime()
                );
                $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::MOVEMENT_POINT_CONVERSION);
            }

            $this->triggerPlayerModifierEvent($player, PlayerModifierEventInterface::MOVEMENT_POINT_MODIFIER, -$movementPointCost);
        }

        if (($moralPointCost = $this->getTotalMoralPointCost($player, $action)) > 0) {
            $this->triggerPlayerModifierEvent($player, PlayerModifierEventInterface::MORAL_POINT_MODIFIER, -$moralPointCost);
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

    private function triggerPlayerModifierEvent(Player $player, string $eventName, int $delta): void
    {
        $playerModifierEvent = new PlayerModifierEventInterface(
            $player,
            $delta,
            'action_cost', //@TODO fix that
            new \DateTime()
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($playerModifierEvent, $eventName);
    }
}
