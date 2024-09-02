<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;

class ActionSideEffectsService implements ActionSideEffectsServiceInterface
{
    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService,
    ) {
        $this->eventService = $eventService;
    }

    public function handleActionSideEffect(
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        Player $player,
        ?LogParameterInterface $actionTarget
    ): Player {
        $this->handleDirty($actionConfig, $actionProvider, $player, $actionTarget);
        $this->handleInjury($actionConfig, $actionProvider, $player, $actionTarget);

        return $player;
    }

    private function handleDirty(
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        Player $player,
        ?LogParameterInterface $actionTarget
    ): void {
        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        $actionEvent = new ActionVariableEvent(
            actionConfig: $actionConfig,
            actionProvider: $actionProvider,
            variableName: ActionVariableEnum::PERCENTAGE_DIRTINESS,
            quantity: $actionConfig->getGameVariables()->getValueByName(ActionVariableEnum::PERCENTAGE_DIRTINESS),
            player: $player,
            tags: $actionConfig->getActionTags(),
            actionTarget: $actionTarget
        );

        $this->eventService->callEvent($actionEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);
    }

    private function handleInjury(
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        Player $player,
        ?LogParameterInterface $actionTarget
    ): void {
        $actionEvent = new ActionVariableEvent(
            actionConfig: $actionConfig,
            actionProvider: $actionProvider,
            variableName: ActionVariableEnum::PERCENTAGE_INJURY,
            quantity: $actionConfig->getGameVariables()->getValueByName(ActionVariableEnum::PERCENTAGE_INJURY),
            player: $player,
            tags: $actionConfig->getActionTags(),
            actionTarget: $actionTarget
        );

        $this->eventService->callEvent($actionEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);
    }
}
