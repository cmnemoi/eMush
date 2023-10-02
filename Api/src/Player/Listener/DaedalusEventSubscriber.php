<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{   
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $playersToKill = $event->getDaedalus()->getPlayers()->getPlayerAlive()->filter(
            fn (Player $player) => $player->isInSpaceBattle()
        );

        foreach ($playersToKill as $player) {
            $playerDeathEvent = new PlayerEvent(
                $player,
                $event->getTags(),
                $event->getTime(),
            );
            $this->eventService->callEvent($playerDeathEvent, PlayerEvent::DEATH_PLAYER);
        }
    }
}