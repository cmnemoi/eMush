<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Project\Enum\ProjectName;
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
            // Handle event before exploration is deleted
            DaedalusEvent::TRAVEL_LAUNCHED => ['onTravelLaunched', EventPriorityEnum::HIGH],
        ];
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->killPlayersOnPlanet($event);

        if ($daedalus->projectIsNotFinished(ProjectName::MAGNETIC_NET)) {
            $this->killPlayersInSpaceBattle($event);
        }
    }

    private function killPlayersOnPlanet(DaedalusEvent $event): void
    {
        $playersToKill = $event->getDaedalus()->getPlayers()->getPlayerAlive()->filter(
            static fn (Player $player) => $player->isExploringOrIsLostOnPlanet()
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

    private function killPlayersInSpaceBattle(DaedalusEvent $event): void
    {
        $playersToKill = $event->getDaedalus()->getPlayers()->getPlayerAlive()->filter(
            static fn (Player $player) => $player->isInSpaceBattle()
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
