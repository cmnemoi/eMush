<?php

declare(strict_types=1);

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Daedalus\Service\DispatchCycleIncidentsService;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\DifficultyServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum as EnumEndCauseEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Lock\LockFactory;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    public const int CYCLE_OXYGEN_LOSS = -3;
    public const string BASE_DAEDALUS_CYCLE_CHANGE = 'base_daedalus_cycle_change';
    public const int LOBBY_TIME_LIMIT = 3 * 24 * 60;

    private DaedalusServiceInterface $daedalusService;
    private DispatchCycleIncidentsService $dispatchCycleIncidents;
    private DifficultyServiceInterface $difficultyService;
    private EventServiceInterface $eventService;
    private LockFactory $lockFactory;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        DispatchCycleIncidentsService $daedalusIncidentDecayService,
        DifficultyServiceInterface $difficultyService,
        EventServiceInterface $eventService,
        LockFactory $lockFactory,
    ) {
        $this->daedalusService = $daedalusService;
        $this->dispatchCycleIncidents = $daedalusIncidentDecayService;
        $this->difficultyService = $difficultyService;
        $this->eventService = $eventService;
        $this->lockFactory = $lockFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => [
                ['updateDaedalusCycle', EventPriorityEnum::HIGHEST],
                ['updateDaedalusDifficulty', EventPriorityEnum::HIGHEST],
                ['applyDaedalusEndCycle', EventPriorityEnum::DAEDALUS_VARIABLES],
                ['dispatchNewCycleIncidents', EventPriorityEnum::DAEDALUS_INCIDENTS],
                ['attributeTitles', EventPriorityEnum::ATTRIBUTE_TITTLES], // do this after all cycle change events to prevent titles being attributed to dead players
            ],
        ];
    }

    public function updateDaedalusCycle(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_cycle');
        $lock->acquire(true);

        try {
            $this->updateDaedalusCycleJob($event);
        } finally {
            $lock->release();
        }
    }

    public function updateDaedalusDifficulty(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_difficulty');
        $lock->acquire(true);

        try {
            $this->difficultyService->updateDaedalusDifficulty($event->getDaedalus());
        } finally {
            $lock->release();
        }
    }

    public function applyDaedalusEndCycle(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_end_cycle');
        $lock->acquire(true);

        try {
            $this->applyDaedalusEndCycleJob($event);
        } finally {
            $lock->release();
        }
    }

    public function dispatchNewCycleIncidents(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_new_cycle_incidents');
        $lock->acquire(true);

        try {
            $this->dispatchNewCycleIncidentsJob($event);
        } finally {
            $lock->release();
        }
    }

    public function attributeTitles(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_attribute_titles');
        $lock->acquire(true);

        try {
            $daedalus = $event->getDaedalus();
            if ($daedalus->getGameStatus() !== GameStatusEnum::CURRENT) {
                return;
            }

            $this->daedalusService->attributeTitles($daedalus, $event->getTime());
        } finally {
            $lock->release();
        }
    }

    private function applyDaedalusEndCycleJob(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $time = $event->getTime();

        $this->dispatchCycleOxygenLoss($daedalus, $time);
        $this->endLobby($daedalus, $time);

        if ($event->hasTag(EventEnum::NEW_DAY)) {
            $this->resetSpores($event);
            $this->resetDailyActionPoints($event);
        }

        if ($daedalus->getOxygen() <= 0) {
            $this->daedalusService->getRandomAsphyxia($daedalus, $time);
        }
    }

    private function dispatchNewCycleIncidentsJob(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $time = $event->getTime();

        if ($this->finishDaedalusIfNoHumanIsAlive($daedalus, $time)) {
            return;
        }

        $this->dispatchCycleIncidents->execute($daedalus, $time);
    }

    private function updateDaedalusCycleJob(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalusConfig = $daedalus->getGameConfig()->getDaedalusConfig();

        if ($daedalus->getCycle() === $daedalusConfig->getCyclePerGameDay()) {
            $daedalus->incrementDay();
            $event->addTag(EventEnum::NEW_DAY);
        } else {
            $daedalus->incrementCycle();
        }
        $this->daedalusService->persist($daedalus);
    }

    private function resetSpores(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $dailySpores = $daedalus->getVariableByName(DaedalusVariableEnum::SPORE)->getMinValueOrThrow();

        $daedalus->setSpores($dailySpores);

        $this->daedalusService->persist($daedalus);
    }

    private function resetDailyActionPoints(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalus->setDailyActionSpent(0);
        $this->daedalusService->persist($daedalus);
    }

    private function finishDaedalusIfNoHumanIsAlive(Daedalus $daedalus, \DateTime $time): bool
    {
        if ($daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->isEmpty()
            && !$daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->isEmpty()
        ) {
            $endDaedalusEvent = new DaedalusEvent(
                $daedalus,
                [EnumEndCauseEnum::KILLED_BY_NERON],
                $time
            );
            $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);

            return true;
        }

        return false;
    }

    private function dispatchCycleOxygenLoss(Daedalus $daedalus, \DateTime $time): void
    {
        $oxygenLoss = self::CYCLE_OXYGEN_LOSS;

        $daedalusEvent = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            $oxygenLoss,
            [EventEnum::NEW_CYCLE, self::BASE_DAEDALUS_CYCLE_CHANGE],
            $time
        );
        $this->eventService->callEvent($daedalusEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function endLobby(Daedalus $daedalus, \DateTime $time): void
    {
        if ($daedalus->isFilling() && $daedalus->getGameDate()->moreThanOrEqualMinutes(self::LOBBY_TIME_LIMIT)) {
            $daedalusEvent = new DaedalusEvent(
                $daedalus,
                [EventEnum::NEW_CYCLE],
                $time
            );
            $this->eventService->callEvent($daedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        }
    }
}
