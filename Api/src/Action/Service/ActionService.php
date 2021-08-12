<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameter;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActionService implements ActionServiceInterface
{
    public const MAX_PERCENT = 99;
    public const BASE_MOVEMENT_POINT_CONVERSION = 3;

    private EventDispatcherInterface $eventDispatcher;
    private ModifierServiceInterface $modifierService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ModifierServiceInterface $modifierService,
        StatusServiceInterface $statusService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->modifierService = $modifierService;
        $this->statusService = $statusService;
    }

    public function applyCostToPlayer(Player $player, Action $action, ?ActionParameter $parameter): Player
    {
        if (($actionPointCost = $this->getTotalActionPointCost($player, $action, $parameter)) > 0) {
            $this->triggerPlayerModifierEvent($player, PlayerModifierEvent::ACTION_POINT_MODIFIER, -$actionPointCost);
        }

        if (($movementPointCost = $this->getTotalMovementPointCost($player, $action, $parameter)) > 0) {
            if ($player->getMovementPoint() === 0) {
                $playerModifierEvent = new PlayerModifierEvent($player, self::BASE_MOVEMENT_POINT_CONVERSION, new \DateTime());
                $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MOVEMENT_POINT_CONVERSION);
            }

            $this->triggerPlayerModifierEvent($player, PlayerModifierEvent::MOVEMENT_POINT_MODIFIER, -$movementPointCost);
        }

        if (($moralPointCost = $this->getTotalMoralPointCost($player, $action, $parameter)) > 0) {
            $this->triggerPlayerModifierEvent($player, PlayerModifierEvent::MORAL_POINT_MODIFIER, -$moralPointCost);
        }

        return $player;
    }

    public function getTotalActionPointCost(Player $player, Action $action, ?ActionParameter $parameter): int
    {
        return $this->modifierService->getActionModifiedValue(
            $action,
            $player,
            ModifierTargetEnum::ACTION_POINT,
            $parameter,
        );
    }

    public function getTotalMovementPointCost(Player $player, Action $action, ?ActionParameter $parameter): int
    {
        return $this->modifierService->getActionModifiedValue(
            $action,
            $player,
            ModifierTargetEnum::MOVEMENT_POINT,
            $parameter,
        );
    }

    public function getTotalMoralPointCost(Player $player, Action $action, ?ActionParameter $parameter): int
    {
        return $this->modifierService->getActionModifiedValue(
            $action,
            $player,
            ModifierTargetEnum::MORAL_POINT,
            $parameter,
        );
    }

    public function getSuccessRate(
        Action $action,
        Player $player,
        ?ActionParameter $parameter
    ): int {
        //Get number of attempt
        $numberOfAttempt = $this->getNumberOfAttempt($player, $action->getName());

        //Get modifiers
        $modifiedValue = $this->modifierService->getActionModifiedValue(
            $action,
            $player,
            ModifierTargetEnum::PERCENTAGE,
            $parameter,
            $numberOfAttempt
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
