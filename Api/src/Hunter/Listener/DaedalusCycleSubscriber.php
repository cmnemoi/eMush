<?php

declare(strict_types=1);

namespace Mush\Hunter\Listener;

use Mush\Communications\Enum\TradeEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Service\GetHolidayForDaedalusService;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Hunter\Service\CreateHunterService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Lock\LockFactory;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private LockFactory $lockFactory;

    public function __construct(
        EventServiceInterface $eventService,
        LockFactory $lockFactory,
        private GetHolidayForDaedalusService $getHolidayForDaedalusService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private CreateHunterService $createHunterService,
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
            $this->handleHuntersNewCycle($event);
            $this->handleSummerEvent($event);
        } finally {
            $lock->release();
        }
    }

    private function handleHuntersNewCycle(DaedalusCycleEvent $event): void
    {
        $event = new HunterCycleEvent($event->getDaedalus(), $event->getTags(), $event->getTime());
        $this->eventService->callEvent($event, HunterCycleEvent::HUNTER_NEW_CYCLE);
    }

    /**
     * If new day during summer event, transport event do not exist, no tablet and under day 6, spawn another transport to give another chance to the players to do the event.
     */
    private function handleSummerEvent(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $filledTime = $daedalus->getFilledAt();

        if (
            $event->hasTag(EventEnum::NEW_DAY)
            && $filledTime
            && $this->getHolidayForDaedalusService->execute($daedalus, $filledTime) === HolidayEnum::SUMMER_TREASURE_HUNT
            && $daedalus->getDay() <= 6
            && $daedalus->getHuntersAroundDaedalus()->getAllHuntersByType(HunterEnum::SUMMER_EVENT_TRANSPORT)->isEmpty()
            && $this->gameEquipmentService->findByNameAndDaedalus(ItemEnum::TREASURE_HUNT_TABLET, $daedalus)->isEmpty()
        ) {
            $this->createHunterService->execute(HunterEnum::SUMMER_EVENT_TRANSPORT, $event->getDaedalus()->getId(), $event->getTime(), [TradeEnum::TREASURE_HUNT_DEAL]);
        }
    }
}
