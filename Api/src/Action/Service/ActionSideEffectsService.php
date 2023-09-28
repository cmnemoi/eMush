<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
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

    public function handleActionSideEffect(Action $action, Player $player, ?LogParameterInterface $actionSupport): Player
    {
        $this->handleDirty($action, $player, $actionSupport);
        $this->handleInjury($action, $player, $actionSupport);

        return $player;
    }

    private function handleDirty(Action $action, Player $player, ?LogParameterInterface $actionSupport): void
    {
        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        $actionEvent = new ActionVariableEvent(
            $action,
            ActionVariableEnum::PERCENTAGE_DIRTINESS,
            $action->getGameVariables()->getValueByName(ActionVariableEnum::PERCENTAGE_DIRTINESS),
            $player,
            $actionSupport
        );

        $this->eventService->callEvent($actionEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);
    }

    private function handleInjury(Action $action, Player $player, ?LogParameterInterface $actionSupport): void
    {
        $actionEvent = new ActionVariableEvent(
            $action,
            ActionVariableEnum::PERCENTAGE_INJURY,
            $action->getGameVariables()->getValueByName(ActionVariableEnum::PERCENTAGE_INJURY),
            $player,
            $actionSupport
        );

        $this->eventService->callEvent($actionEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);
    }
}
