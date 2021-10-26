<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActionService implements ActionServiceInterface
{
    public const MAX_PERCENT = 99;
    public const BASE_MOVEMENT_POINT_CONVERSION_GAIN = 3;
    public const BASE_MOVEMENT_POINT_CONVERSION_COST = 1;

    private EventDispatcherInterface $eventDispatcher;
    private ModifierServiceInterface $modifierService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ModifierServiceInterface $modifierService,
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->modifierService = $modifierService;
    }

    public function applyCostToPlayer(Player $player, Action $action, ?LogParameterInterface $parameter): Player
    {
        if (($actionPointCost = $this->getTotalActionPointCost($player, $action, $parameter)) > 0) {
            $this->triggerPlayerModifierEvent($player, PlayerModifierEvent::ACTION_POINT_MODIFIER, -$actionPointCost);
        }

        if (($movementPointCost = $this->getTotalMovementPointCost($player, $action, $parameter)) > 0) {
            $missingMovementPoints = $action->getActionCost()->getMovementPointCost() - $player->getMovementPoint();
            if ($missingMovementPoints > 0) {
                $movementPointGain = $this->getMovementPointConversionGain($player, true);
                $numberOfConversions = (int) ceil($missingMovementPoints / $movementPointGain);

                $conversionGain = $numberOfConversions * $movementPointGain;

                $playerModifierEvent = new PlayerModifierEvent(
                    $player,
                    $conversionGain,
                    ActionEnum::MOVE,
                    new \DateTime()
                );
                $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MOVEMENT_POINT_MODIFIER);
            }

            $this->triggerPlayerModifierEvent($player, PlayerModifierEvent::MOVEMENT_POINT_MODIFIER, -$movementPointCost);
        }

        if (($moralPointCost = $this->getTotalMoralPointCost($player, $action, $parameter)) > 0) {
            $this->triggerPlayerModifierEvent($player, PlayerModifierEvent::MORAL_POINT_MODIFIER, -$moralPointCost);
        }

        return $player;
    }

    private function getMovementPointConversionCost(Player $player, bool $consumeCharge = false): int
    {
        return $this->modifierService->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION],
            ModifierTargetEnum::ACTION_POINT,
            self::BASE_MOVEMENT_POINT_CONVERSION_COST,
            $consumeCharge
        );
    }

    private function getMovementPointConversionGain(Player $player, bool $consumeCharge = false): int
    {
        return $this->modifierService->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION],
            ModifierTargetEnum::MOVEMENT_POINT,
            self::BASE_MOVEMENT_POINT_CONVERSION_GAIN,
            $consumeCharge
        );
    }

    public function getTotalActionPointCost(
        Player $player,
        Action $action,
        ?LogParameterInterface $parameter,
        bool $consumeCharge = false
    ): int {
        $conversionCost = 0;
        $missingMovementPoints = $action->getActionCost()->getMovementPointCost() - $player->getMovementPoint();
        if ($missingMovementPoints > 0) {
            $numberOfConversions = (int) ceil($missingMovementPoints / $this->getMovementPointConversionGain($player));

            $conversionCost = $numberOfConversions * $this->getMovementPointConversionCost($player, $consumeCharge);
        }

        return $this->modifierService->getActionModifiedValue(
            $action,
            $player,
            ModifierTargetEnum::ACTION_POINT,
            $parameter,
        ) + $conversionCost;
    }

    public function getTotalMovementPointCost(
        Player $player,
        Action $action,
        ?LogParameterInterface $parameter,
    ): int {
        return $this->modifierService->getActionModifiedValue(
            $action,
            $player,
            ModifierTargetEnum::MOVEMENT_POINT,
            $parameter,
        );
    }

    public function getTotalMoralPointCost(
        Player $player,
        Action $action,
        ?LogParameterInterface $parameter,
    ): int {
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
        ?LogParameterInterface $parameter
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

    private function triggerPlayerModifierEvent(Player $player, string $eventName, int $delta): void
    {
        $playerModifierEvent = new PlayerModifierEvent(
            $player,
            $delta,
            'action_cost', //@TODO fix that
            new \DateTime()
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($playerModifierEvent, $eventName);
    }
}
