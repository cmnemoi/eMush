<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerVariableSubscriber implements EventSubscriberInterface
{
    private PlayerVariableServiceInterface $playerVariableService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        PlayerVariableServiceInterface $playerVariableService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->playerVariableService = $playerVariableService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            AbstractQuantityEvent::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(AbstractQuantityEvent $playerEvent): void
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
                $this->handleHealthPointModifier($player, $playerEvent->getReason(), $playerEvent->getTime());

                return;

            case PlayerVariableEnum::SPORE:
                $this->handleInfection($player, $playerEvent);

                return;
        }
    }

    private function handleHealthPointModifier(Player $player, string $reason, \DateTime $time): void
    {
        $deathCause = EndCauseEnum::DEATH_CAUSE_MAP;

        if ($player->getHealthPoint() <= 0) {
            $deathReason = EndCauseEnum::INJURY;

            if (isset($deathCause[$reason])) {
                $deathReason = $deathCause[$reason];
            }

            // To be more clear of what's happening
            $deathEvent = new PlayerEvent(
                $player,
                $deathReason,
                $time
            );

            $this->eventDispatcher->dispatch($deathEvent, PlayerEvent::DEATH_PLAYER);
        }
    }

    private function handleInfection(Player $player, PlayerEvent $playerEvent): void
    {
        if ($player->getSpores() === $this->playerVariableService->getMaxPlayerVariable($player, PlayerVariableEnum::SPORE)) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CONVERSION_PLAYER);
        }

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);
    }
}
