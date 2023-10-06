<?php

namespace Mush\Hunter\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterCycleSubscriber implements EventSubscriberInterface
{
    private CycleServiceInterface $cycleService;
    private EventServiceInterface $eventService;
    private HunterServiceInterface $hunterService;
    private RandomServiceInterface $randomService;

    public function __construct(
        CycleServiceInterface $cycleService,
        EventServiceInterface $eventService,
        HunterServiceInterface $hunterService,
        RandomServiceInterface $randomService
    ) {
        $this->cycleService = $cycleService;
        $this->eventService = $eventService;
        $this->hunterService = $hunterService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HunterCycleEvent::HUNTER_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(HunterCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $attackingHunters = $daedalus->getAttackingHunters();
        $this->hunterService->makeHuntersShoot($attackingHunters);

        $currentTime = $event->getTime();
        if (!$this->canHuntersSpawn($daedalus, $currentTime)) {
            return;
        }

        $hunterSpawnRate = $daedalus->getGameConfig()->getDifficultyConfig()->getHunterSpawnRate();
        if ($this->randomService->isSuccessful($hunterSpawnRate)) {
            $unpoolHunterEvent = new HunterPoolEvent(
                $daedalus,
                $event->getTags(),
                $currentTime
            );

            $this->eventService->callEvent($unpoolHunterEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        }
    }

    private function canHuntersSpawn(Daedalus $daedalus, \DateTime $currentTime): bool
    {
        $cycleStartedAt = $daedalus->getCycleStartedAt();
        if ($cycleStartedAt === null) {
            throw new \Exception('Daedalus should have a cycle started at');
        }
        $nbCyclesElapsed = $this->cycleService->getNumberOfCycleElapsed(
            $cycleStartedAt,
            $currentTime,
            $daedalus
        );
        $truceCycles = $daedalus->getGameConfig()->getDifficultyConfig()->getStartingHuntersNumberOfTruceCycles();

        return !$daedalus->isInHunterSafeCycle() || $nbCyclesElapsed > $truceCycles;
    }
}
