<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\QuantityEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerVariableSubscriber implements EventSubscriberInterface
{
    private PlayerVariableServiceInterface $playerVariableService;
    private EventServiceInterface $eventService;

    public function __construct(
        PlayerVariableServiceInterface $playerVariableService,
        EventServiceInterface $eventService
    ) {
        $this->playerVariableService = $playerVariableService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents()
    {
        return [
            QuantityEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(QuantityEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();
        $variableName = $playerEvent->getModifiedVariable();

        $this->playerVariableService->handleGameVariableChange($variableName, $delta, $player);

        switch ($playerEvent->getModifiedVariable()) {
            case PlayerVariableEnum::HEALTH_POINT:
                $this->handleHealthPointModifier($player, $playerEvent, $playerEvent->getTime());

                return;

            case PlayerVariableEnum::SPORE:
                $this->handleInfection($player, $playerEvent);

                return;
        }
    }

    private function handleHealthPointModifier(Player $player, PlayerVariableEvent $event, \DateTime $time): void
    {
        if ($player->getHealthPoint() <= 0) {
            $deathReason = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);

            if ($deathReason === null) {
                $event->addTag(EndCauseEnum::INJURY);
            }

            $this->eventService->callEvent($event, PlayerEvent::DEATH_PLAYER);
        }
    }

    private function handleInfection(Player $player, PlayerEvent $playerEvent): void
    {
        if ($player->getSpores() === $this->playerVariableService->getMaxPlayerVariable($player, PlayerVariableEnum::SPORE)) {
            $this->eventService->callEvent($playerEvent, PlayerEvent::CONVERSION_PLAYER);
        }

        $this->eventService->callEvent($playerEvent, PlayerEvent::INFECTION_PLAYER);
    }
}
