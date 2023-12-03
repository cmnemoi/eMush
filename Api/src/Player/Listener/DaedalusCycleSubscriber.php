<?php

namespace Mush\Player\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService
    ) {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::PLAYERS],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        foreach ($event->getDaedalus()->getPlayers()->getPlayerAlive() as $player) {
            $newPlayerCycle = new PlayerCycleEvent(
                $player,
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($newPlayerCycle, PlayerCycleEvent::PLAYER_NEW_CYCLE);
        }
    }
}
