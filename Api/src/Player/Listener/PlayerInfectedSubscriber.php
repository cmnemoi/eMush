<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerInfectedEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerInfectedSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private PlayerVariableServiceInterface $playerVariableService;
    private EventServiceInterface $eventService;

    public function __construct(
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService,
        EventServiceInterface $eventService
    ) {
        $this->playerService = $playerService;
        $this->playerVariableService = $playerVariableService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(VariableEventInterface $playerInfectedEvent): void
    {
        if (!$playerInfectedEvent instanceof PlayerInfectedEvent) {
            return;
        }

        $player = $playerInfectedEvent->getPlayer();
        $delta = $playerInfectedEvent->getQuantity();
        $variableName = $playerInfectedEvent->getVariableName();

        if ($playerInfectedEvent->getQuantity() > 0 && !$player->isMush()) {
            $this->eventService->callEvent($playerInfectedEvent, PlayerEvent::INFECTION_PLAYER);
        }

        if ($player->getVariableByName(PlayerVariableEnum::SPORE)->isMax() && !$player->isMush()) {
            $this->eventService->callEvent($playerInfectedEvent, PlayerEvent::CONVERSION_PLAYER);
        }
    }
}
