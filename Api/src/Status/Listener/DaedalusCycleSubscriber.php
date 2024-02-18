<?php

namespace Mush\Status\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Event\StatusCycleEvent;
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
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::HIGH],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_cycle');
        $lock->acquire(true);

        try {
            $daedalus = $event->getDaedalus();
            /** @var Status $status */
            foreach ($daedalus->getStatuses() as $status) {
                $statusNewCycle = new StatusCycleEvent(
                    $status,
                    $daedalus,
                    $event->getTags(),
                    $event->getTime()
                );
                $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
            }
        } finally {
            $lock->release();
        }
    }
}
