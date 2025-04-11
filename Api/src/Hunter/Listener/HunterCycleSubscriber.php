<?php

declare(strict_types=1);

namespace Mush\Hunter\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Hunter\Service\DeleteTransportService;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class HunterCycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CreateHunterService $createHunter,
        private CycleServiceInterface $cycleService,
        private D100RollServiceInterface $d100Roll,
        private DeleteTransportService $deleteTransport,
        private EventServiceInterface $eventService,
        private HunterServiceInterface $hunterService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            HunterCycleEvent::HUNTER_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(HunterCycleEvent $event): void
    {
        $this->deleteAggroedTransports($event);
        $this->makeHuntersShoot($event);
        $this->tryToSpawnTransport($event);
        $this->tryToSpawnHunters($event);
    }

    private function deleteAggroedTransports(HunterCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $aggroedTransports = $daedalus->getHuntersAroundDaedalus()->getAggroedTransports();
        foreach ($aggroedTransports as $aggroedTransport) {
            $this->deleteTransport->byId($aggroedTransport->getId(), $event->getTags());
        }
    }

    private function makeHuntersShoot(HunterCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $attackingHunters = $daedalus->getAttackingHunters();
        $this->hunterService->makeHuntersShoot($attackingHunters);
    }

    private function tryToSpawnTransport(HunterCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $minTransportSpawnRate = $daedalus->getGameConfig()->getDifficultyConfig()->getMinTransportSpawnRate();
        $maxTransportSpawnRate = $daedalus->getGameConfig()->getDifficultyConfig()->getMaxTransportSpawnRate();
        $transportSpawnRate = max($minTransportSpawnRate, $maxTransportSpawnRate - $daedalus->getDay());
        if ($this->d100Roll->isSuccessful($transportSpawnRate)) {
            $this->createHunter->execute(HunterEnum::TRANSPORT, $daedalus->getId());
        }
    }

    private function tryToSpawnHunters(HunterCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $currentTime = $event->getTime();

        if (!$this->canHuntersSpawn($daedalus, $currentTime)) {
            return;
        }

        $hunterSpawnRate = $daedalus->getGameConfig()->getDifficultyConfig()->getHunterSpawnRate();
        if ($this->d100Roll->isAFailure($hunterSpawnRate)) {
            return;
        }

        $unpoolHunterEvent = new HunterPoolEvent(
            $daedalus,
            $event->getTags(),
            $currentTime
        );
        $this->eventService->callEvent($unpoolHunterEvent, HunterPoolEvent::UNPOOL_HUNTERS);
    }

    private function canHuntersSpawn(Daedalus $daedalus, \DateTime $currentTime): bool
    {
        $nbCyclesElapsed = $this->cycleService->getNumberOfCycleElapsed(
            $daedalus->getCycleStartedAtOrThrow(),
            $currentTime,
            $daedalus->getDaedalusInfo(),
        );
        $truceCycles = $daedalus->getGameConfig()->getDifficultyConfig()->getStartingHuntersNumberOfTruceCycles();

        return !$daedalus->isInHunterSafeCycle() || $nbCyclesElapsed > $truceCycles;
    }
}
