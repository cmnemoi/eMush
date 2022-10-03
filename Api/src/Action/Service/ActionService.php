<?php

namespace Mush\Action\Service;

use LogicException;
use Mush\Action\Entity\Action;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Event\ResourcePointChangeEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;

class ActionService implements ActionServiceInterface
{
    public const MAX_PERCENT = 99;
    public const ATTEMPT_INCREASE = 1.25;
    public const BASE_MOVEMENT_POINT_CONVERSION_GAIN = 1;
    public const BASE_MOVEMENT_POINT_CONVERSION_COST = 1;
    public const IMPOSSIBLE_ACTION_COST = 9293;

    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public function applyCostToPlayer(Player $player, Action $action, ?LogParameterInterface $parameter): Player
    {
        if (($actionPointCost = $this->getTotalActionPointCost($player, $action)) > 0) {
            $this->applyCost($player, -$actionPointCost, PlayerVariableEnum::ACTION_POINT);
        }

        if (($movementPointCost = $this->getTotalMovementPointCost($player, $action)) > 0) {
            $missingMovementPoints = $movementPointCost - $player->getMovementPoint();

            if ($missingMovementPoints > 0) {
                $movementPointGain = $this->getMovementPointConversionGain($player, $action);
                if ($movementPointGain <= 0) {
                    throw new LogicException('The Player can\'t pay this cost');
                }
                $numberOfConversions = (int) ceil($missingMovementPoints / $movementPointGain);

                $conversionGain = $numberOfConversions * $movementPointGain;
                $this->applyCost(
                    $player,
                    $conversionGain,
                    PlayerVariableEnum::MOVEMENT_POINT,
                    PlayerVariableEvent::CONVERT_ACTION_TO_MOVEMENT_POINT
                );
            }

            $this->applyCost($player, -$movementPointCost, PlayerVariableEnum::MOVEMENT_POINT);
        }

        if (($moralPointCost = $this->getTotalMoralPointCost($player, $action)) > 0) {
            $this->applyCost($player, -$moralPointCost, PlayerVariableEnum::MORAL_POINT);
        }

        return $player;
    }

    private function applyCost(Player $player, int $delta, string $costType, string $reason = PlayerVariableEvent::ACTION_COST): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            $costType,
            $delta,
            $reason,
            new \DateTime()
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    public function getTotalActionPointCost(Player $player, Action $action): int
    {
        $actionPointsCost = 0;
        $missingMovementPoints = $this->getTotalMovementPointCost($player, $action) - $player->getMovementPoint();
        if ($missingMovementPoints > 0) {
            $conversionGain = $this->getMovementPointConversionGain($player, $action);
            if ($conversionGain <= 0) {
                return self::IMPOSSIBLE_ACTION_COST;
            }
            $numberOfConversions = (int) ceil($missingMovementPoints / $this->getMovementPointConversionGain($player, $action));

            $actionPointsCost = $numberOfConversions * $this->getMovementPointConversionCost($player, $action);
        }

        return $this->getPointFromResourceChange(
            $player,
            PlayerVariableEnum::ACTION_POINT,
            $action->getActionCost()->getVariableCost(PlayerVariableEnum::ACTION_POINT),
            $action,
            ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT
        ) + $actionPointsCost;
    }

    public function getTotalMovementPointCost(Player $player, Action $action): int
    {
        return $this->getPointFromResourceChange(
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            $action->getActionCost()->getVariableCost(PlayerVariableEnum::MOVEMENT_POINT),
            $action,
            ResourcePointChangeEvent::CHECK_CHANGE_MOVEMENT_POINT
        );
    }

    public function getTotalMoralPointCost(Player $player, Action $action): int
    {
        return $this->getPointFromResourceChange(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            $action->getActionCost()->getVariableCost(PlayerVariableEnum::MORAL_POINT),
            $action,
            ResourcePointChangeEvent::CHECK_CHANGE_MORAL_POINT
        );
    }

    public function getSuccessRate(Action $action, Player $player): int
    {
        $numberOfAttempt = $this->getNumberOfAttempt($player, $action->getName());
        $initialValue = intval($action->getSuccessRate() * self::ATTEMPT_INCREASE ** $numberOfAttempt);

        $event = new PreparePercentageRollEvent(
            $player,
            $initialValue,
            $action->getName(),
            new \DateTime()
        );
        $this->eventService->callEvent($event, PreparePercentageRollEvent::ACTION_ROLL_RATE);

        return min($this::MAX_PERCENT, $event->getRate());
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

    private function getMovementPointConversionCost(Player $player, Action $action): int
    {
        return $this->getPointFromResourceChange(
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            self::BASE_MOVEMENT_POINT_CONVERSION_COST,
            $action,
            ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_COST
        );
    }

    private function getMovementPointConversionGain(Player $player, Action $action): int
    {
        return $this->getPointFromResourceChange(
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            self::BASE_MOVEMENT_POINT_CONVERSION_GAIN,
            $action,
            ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_GAIN
        );
    }

    private function getPointFromResourceChange(
        Player $player,
        string $variablePoint,
        int $cost,
        Action $action,
        string $reason
    ): int {
        codecept_debug('la');
        codecept_debug($cost);
        $event = new ResourcePointChangeEvent(
            $player,
            $variablePoint,
            $cost,
            $action->getName(),
            new \DateTime()
        );
        $this->eventService->callEvent($event, $reason);

        codecept_debug($event->getCost());

        return $event->getCost();
    }
}
