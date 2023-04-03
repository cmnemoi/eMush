<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;

class ActionService implements ActionServiceInterface
{
    public const BASE_MOVEMENT_POINT_CONVERSION_GAIN = -2;
    public const BASE_MOVEMENT_POINT_CONVERSION_COST = 1;

    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService,
    ) {
        $this->eventService = $eventService;
    }

    public function applyCostToPlayer(Player $player, Action $action, ?LogParameterInterface $parameter): Player
    {
        // Action point
        $actionPointCostEvent = $this->getActionEvent($player, $action, $parameter, PlayerVariableEnum::ACTION_POINT);
        $this->eventService->callEvent($actionPointCostEvent, ActionVariableEvent::APPLY_COST);

        // Moral Point
        $moralPointCostEvent = $this->getActionEvent($player, $action, $parameter, PlayerVariableEnum::MORAL_POINT);
        $this->eventService->callEvent($moralPointCostEvent, ActionVariableEvent::APPLY_COST);

        // Movement points : need to handle conversion events
        $movementPointCostEvent = $this->getActionEvent($player, $action, $parameter, PlayerVariableEnum::MOVEMENT_POINT);
        /** @var ActionVariableEvent $movementPointCostEvent */
        $movementPointCostEvent = $this->eventService->previewEvent($movementPointCostEvent, ActionVariableEvent::APPLY_COST);

        $movementPointCost = $movementPointCostEvent->getQuantity();
        $missingMovementPoints = $movementPointCost - $player->getMovementPoint();
        if ($missingMovementPoints > 0) {
            $this->handleConversionEvents($player, $action, $parameter, $missingMovementPoints, true);
        }

        $this->eventService->callEvent($movementPointCostEvent, ActionVariableEvent::APPLY_COST);

        return $player;
    }

    private function handleConversionEvents(
        Player $player,
        Action $action,
        ?LogParameterInterface $parameter,
        int $missingMovementPoints,
        bool $dispatch
    ): int {
        // first get how much movement point each conversion provides
        $conversionGainEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::MOVEMENT_POINT,
            self::BASE_MOVEMENT_POINT_CONVERSION_GAIN,
            $player,
            $parameter
        );
        $conversionGainEvent->addTag(ActionVariableEvent::MOVEMENT_CONVERSION);
        /** @var ActionVariableEvent $conversionGainEvent */
        $conversionGainEvent = $this->eventService->previewEvent($conversionGainEvent, ActionVariableEvent::APPLY_COST);

        // Compute how much conversion are needed to have the required number of movement point for the action
        $movementPointGain = $conversionGainEvent->getQuantity();
        $numberOfConversions = (int) ceil($missingMovementPoints / (-$movementPointGain));

        // How much each conversion is going to cost in action points
        $conversionCostEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::ACTION_POINT,
            self::BASE_MOVEMENT_POINT_CONVERSION_COST,
            $player,
            $parameter
        );
        $conversionCostEvent->addTag(ActionVariableEvent::MOVEMENT_CONVERSION);
        /** @var ActionVariableEvent $conversionCostEvent */
        $conversionCostEvent = $this->eventService->previewEvent($conversionCostEvent, ActionVariableEvent::APPLY_COST);

        if ($dispatch) {
            for ($i = 0; $i < $numberOfConversions; ++$i) {
                $this->eventService->callEvent($conversionCostEvent, ActionVariableEvent::APPLY_COST);
                $this->eventService->callEvent($conversionGainEvent, ActionVariableEvent::APPLY_COST);
            }
        }

        return $numberOfConversions * $conversionCostEvent->getQuantity();
    }

    private function getActionEvent(
        Player $player,
        Action $action,
        ?LogParameterInterface $parameter,
        string $variable
    ): ActionVariableEvent {
        return new ActionVariableEvent(
            $action,
            $variable,
            $action->getGameVariables()->getValueByName($variable),
            $player,
            $parameter
        );
    }

    public function getActionModifiedActionVariable(
        Player $player,
        Action $action,
        ?LogParameterInterface $parameter,
        string $variableName
    ): int {
        if (key_exists($variableName, ActionVariableEvent::VARIABLE_TO_EVENT_MAP)) {
            $eventName = ActionVariableEvent::VARIABLE_TO_EVENT_MAP[$variableName];
        } else {
            throw new \Exception('this key do not exist in this map');
        }
        $variable = $action->getVariableByName($variableName);

        $actionVariableEvent = $this->getActionEvent($player, $action, $parameter, $variableName);
        /** @var ActionVariableEvent $actionVariableEvent */
        $actionVariableEvent = $this->eventService->previewEvent($actionVariableEvent, $eventName);

        $value = $actionVariableEvent->getQuantity();

        // handle the cost of converting action points to movement points
        if ($variableName === PlayerVariableEnum::ACTION_POINT) {
            $movementVariableEvent = $this->getActionEvent($player, $action, $parameter, PlayerVariableEnum::MOVEMENT_POINT);
            /** @var ActionVariableEvent $movementVariableEvent */
            $movementVariableEvent = $this->eventService->previewEvent($movementVariableEvent, ActionVariableEvent::APPLY_COST);

            $missingMovementPoints = $movementVariableEvent->getQuantity() - $player->getMovementPoint();
            if ($missingMovementPoints > 0) {
                $costToAdd = $this->handleConversionEvents($player, $action, $parameter, $missingMovementPoints, false);

                return $value + $costToAdd;
            }
        }

        return $variable->getValueInRange($value);
    }
}
