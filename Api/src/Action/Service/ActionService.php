<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Action\Repository\ActionConfigRepository;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;

class ActionService implements ActionServiceInterface
{
    private EventServiceInterface $eventService;
    private ActionConfigRepository $actionConfigRepository;

    public function __construct(
        EventServiceInterface $eventService,
        ActionConfigRepository $actionConfigRepository
    ) {
        $this->eventService = $eventService;
        $this->actionConfigRepository = $actionConfigRepository;
    }

    public function applyCostToPlayer(
        Player $player,
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        ?LogParameterInterface $actionTarget,
        ActionResult $actionResult
    ): Player {
        // ActionConfig point
        $actionPointCostEvent = $this->getActionEvent(
            $player,
            $actionConfig,
            $actionProvider,
            $actionTarget,
            PlayerVariableEnum::ACTION_POINT
        );
        $this->eventService->callEvent($actionPointCostEvent, ActionVariableEvent::APPLY_COST);

        // Moral Point
        $moralPointCostEvent = $this->getActionEvent(
            $player,
            $actionConfig,
            $actionProvider,
            $actionTarget,
            PlayerVariableEnum::MORAL_POINT
        );
        $this->eventService->callEvent($moralPointCostEvent, ActionVariableEvent::APPLY_COST);

        // Movement points : need to handle conversion events
        $movementPointCostEvent = $this->getActionEvent(
            $player,
            $actionConfig,
            $actionProvider,
            $actionTarget,
            PlayerVariableEnum::MOVEMENT_POINT
        );

        /** @var ActionVariableEvent $movementPointCostEvent */
        $movementPointCostEvent = $this->eventService->computeEventModifications($movementPointCostEvent, ActionVariableEvent::APPLY_COST);

        $movementPointCost = $movementPointCostEvent->getRoundedQuantity();
        $missingMovementPoints = $movementPointCost - $player->getMovementPoint();
        if ($missingMovementPoints > 0) {
            $this->handleConversionEvents($player, $missingMovementPoints, true);
        }

        $this->eventService->callEvent($movementPointCostEvent, ActionVariableEvent::APPLY_COST);

        // we need to call a last event to properly apply modifier logs
        $actionPointCostEvent = $this->getActionEvent(
            $player,
            $actionConfig,
            $actionProvider,
            $actionTarget,
            ActionVariableEnum::OUTPUT_QUANTITY,
            $actionResult
        );
        $this->eventService->callEvent($actionPointCostEvent, ActionVariableEvent::GET_OUTPUT_QUANTITY);

        return $player;
    }

    public function getActionModifiedActionVariable(
        Player $player,
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        ?LogParameterInterface $actionTarget,
        string $variableName
    ): int {
        if (\array_key_exists($variableName, ActionVariableEvent::VARIABLE_TO_EVENT_MAP)) {
            $eventName = ActionVariableEvent::VARIABLE_TO_EVENT_MAP[$variableName];
        } else {
            throw new \Exception('this key do not exist in this map');
        }
        $variable = $actionConfig->getVariableByName($variableName);

        $actionVariableEvent = $this->getActionEvent(
            $player,
            $actionConfig,
            $actionProvider,
            $actionTarget,
            $variableName
        );

        /** @var ActionVariableEvent $actionVariableEvent */
        $actionVariableEvent = $this->eventService->computeEventModifications($actionVariableEvent, $eventName);

        $value = $actionVariableEvent->getRoundedQuantity();

        return $variable->getValueInRange($value);
    }

    public function playerCanAffordPoints(
        Player $player,
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        ?LogParameterInterface $actionTarget
    ): bool {
        $playerAction = $player->getActionPoint();
        $playerMovement = $player->getMovementPoint();
        $playerMorale = $player->getMoralPoint();

        $moraleCost = $this->getActionModifiedActionVariable(
            $player,
            $actionConfig,
            $actionProvider,
            $actionTarget,
            PlayerVariableEnum::MORAL_POINT
        );
        $actionCost = $this->getActionModifiedActionVariable(
            $player,
            $actionConfig,
            $actionProvider,
            $actionTarget,
            PlayerVariableEnum::ACTION_POINT
        );
        $movementCost = $this->getActionModifiedActionVariable(
            $player,
            $actionConfig,
            $actionProvider,
            $actionTarget,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $extraActionPoints = 0;

        if ($playerMorale < $moraleCost) {
            return false;
        }

        if ($playerMovement < $movementCost) {
            $extraActionPoints = $this->handleConversionEvents($player, $movementCost - $playerMovement, false);
        }
        if ($playerAction < $extraActionPoints + $actionCost) {
            return false;
        }

        return true;
    }

    private function handleConversionEvents(
        Player $player,
        int $missingMovementPoints,
        bool $dispatch
    ): int {
        /** @var ActionConfig $convertActionConfig */
        $convertActionConfig = $this->actionConfigRepository->findOneBy([
            'actionName' => ActionEnum::CONVERT_ACTION_TO_MOVEMENT,
        ]);

        // first get how much movement point each conversion provides
        $conversionGainEvent = new ActionVariableEvent(
            $convertActionConfig,
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            $convertActionConfig->getMovementCost(),
            $player,
            null
        );

        /** @var ActionVariableEvent $conversionGainEvent */
        $conversionGainEvent = $this->eventService->computeEventModifications($conversionGainEvent, ActionVariableEvent::APPLY_COST);

        // Compute how much conversion are needed to have the required number of movement point for the action
        $movementPointGain = $conversionGainEvent->getRoundedQuantity();

        if ($movementPointGain === 0) {
            // set to a cost impossible for the player
            return $player->getVariableValueByName(PlayerVariableEnum::ACTION_POINT) + 10;
        }
        $numberOfConversions = (int) ceil($missingMovementPoints / (-$movementPointGain));

        // How much each conversion is going to cost in action points
        $conversionCostEvent = new ActionVariableEvent(
            $convertActionConfig,
            $player,
            PlayerVariableEnum::ACTION_POINT,
            $convertActionConfig->getActionCost(),
            $player,
            null
        );

        /** @var ActionVariableEvent $conversionCostEvent */
        $conversionCostEvent = $this->eventService->computeEventModifications($conversionCostEvent, ActionVariableEvent::APPLY_COST);

        if ($dispatch) {
            for ($i = 0; $i < $numberOfConversions; ++$i) {
                $this->eventService->callEvent($conversionCostEvent, ActionVariableEvent::APPLY_COST);
                $this->eventService->callEvent($conversionGainEvent, ActionVariableEvent::APPLY_COST);
            }
        }

        return $numberOfConversions * $conversionCostEvent->getRoundedQuantity();
    }

    private function getActionEvent(
        Player $player,
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        ?LogParameterInterface $actionTarget,
        string $variable,
        ?ActionResult $result = null
    ): ActionVariableEvent {
        $event = new ActionVariableEvent(
            $actionConfig,
            $actionProvider,
            $variable,
            $actionConfig->getGameVariables()->getValueByName($variable),
            $player,
            $actionTarget
        );
        if ($result) {
            $event->addTag($result->getName());
        }

        return $event;
    }
}
