<?php

namespace Mush\Player\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Lock\LockFactory;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private LockFactory $lockFactory;

    public function __construct(
        EventServiceInterface $eventService,
        LockFactory $lockFactory
    ) {
        $this->eventService = $eventService;
        $this->lockFactory = $lockFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::PLAYERS],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_cycle');
        $lock->acquire(true);

        try {
            foreach ($event->getDaedalus()->getPlayers()->getPlayerAlive() as $player) {
                $newPlayerCycle = new PlayerCycleEvent(
                    $player,
                    $event->getTags(),
                    $event->getTime()
                );
                $this->eventService->callEvent($newPlayerCycle, PlayerCycleEvent::PLAYER_NEW_CYCLE);
            }
        } finally {
            $lock->release();
        }
    }
}
