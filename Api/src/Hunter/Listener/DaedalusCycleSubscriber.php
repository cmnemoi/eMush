<?php

namespace Mush\Hunter\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterCycleEvent;
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
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::HUNTERS],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_cycle');
        $lock->acquire(true);
        try {
            $event = new HunterCycleEvent($event->getDaedalus(), $event->getTags(), $event->getTime());
            $this->eventService->callEvent($event, HunterCycleEvent::HUNTER_NEW_CYCLE);
        } finally {
            $lock->release();
        }
    }
}
