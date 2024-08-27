<?php

namespace Mush\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;

class CycleService implements CycleServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private LockFactory $lockFactory;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        LockFactory $lockFactory,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->lockFactory = $lockFactory;
        $this->logger = $logger;
    }

    public function handleDaedalusAndExplorationCycleChanges(\DateTime $dateTime, Daedalus $daedalus): CycleChangeResult
    {
        $lock = $this->lockFactory->createLock('daedalus_cycle_change');
        if (!$lock->acquire()) {
            return new CycleChangeResult(0, 0);
        }

        try {
            $daedalusCyclesElapsed = $this->handleDaedalusCycleChange($dateTime, $daedalus);
            $exploration = $daedalus->getExploration();
            if ($exploration) {
                $explorationCyclesElapsed = $this->handleExplorationCycleChange($dateTime, $exploration);
            } else {
                $explorationCyclesElapsed = 0;
            }
        } finally {
            $lock->release();
        }

        return new CycleChangeResult($daedalusCyclesElapsed, $explorationCyclesElapsed);
    }

    public function getDateStartNextCycle(Daedalus $daedalus): \DateTime
    {
        $daedalusConfig = $daedalus->getGameConfig()->getDaedalusConfig();

        if (($dateDaedalusLastCycle = $daedalus->getCycleStartedAt()) === null) {
            throw new \LogicException('Daedalus should have a CycleStartedAt Value');
        }

        $nextCycleStartAt = clone $dateDaedalusLastCycle;

        return $nextCycleStartAt->add(new \DateInterval('PT' . $daedalusConfig->getCycleLength() . 'M'));
    }

    // get day cycle from date (value between 1 and $gameConfig->getCyclePerGameDay())
    public function getInDayCycleFromDate(\DateTime $date, ClosedDaedalus|Daedalus $daedalus): int
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();

        $gameConfig = $daedalusInfo->getGameConfig();
        $localizationConfig = $daedalusInfo->getLocalizationConfig();
        $daedalusConfig = $gameConfig->getDaedalusConfig();

        /** @var non-empty-string $timeZone */
        $timeZone = $localizationConfig->getTimeZone();
        $timeZoneDate = $date->setTimezone(new \DateTimeZone($timeZone));
        $minutes = (int) $timeZoneDate->format('i');
        $hours = (int) $timeZoneDate->format('H');

        return (int) (floor(
            ($minutes + $hours * 60) / $daedalusConfig->getCycleLength() + 1
        ) - 1) % $daedalusConfig->getCyclePerGameDay() + 1;
    }

    /**
     * Get Daedalus first cycle date
     * First actual cycle of the ship (ie: for 3h cycle in France, if the ship start C8, then it will be 21h:00).
     */
    public function getDaedalusStartingCycleDate(Daedalus $daedalus): \DateTime
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();

        $timeConfig = $daedalusInfo->getLocalizationConfig();
        $daedalusConfig = $daedalusInfo->getGameConfig()->getDaedalusConfig();

        $firstCycleDate = $daedalus->getCreatedAt() ?? new \DateTime();

        /** @var non-empty-string $timeZone */
        $timeZone = $timeConfig->getTimeZone();
        $firstDayDate = clone $firstCycleDate;
        $firstDayDate
            ->setTimezone(new \DateTimeZone($timeZone))
            ->setTime(0, 0)
            ->setTimezone(new \DateTimeZone('UTC'));

        $gameDayLength = (int) ($daedalusConfig->getCyclePerGameDay() * $daedalusConfig->getCycleLength()); // in min
        $numberOfCompleteDay = (int) ($this->getDateIntervalAsMinutes($firstCycleDate, $firstDayDate) / $gameDayLength);
        $minutesBetweenDayStartAndDaedalusFirstCycle = $numberOfCompleteDay * $gameDayLength + (($daedalus->getCycle() - 1) * $daedalusConfig->getCycleLength());

        return $firstDayDate->add(new \DateInterval('PT' . $minutesBetweenDayStartAndDaedalusFirstCycle . 'M'));
    }

    public function getNumberOfCycleElapsed(\DateTime $start, \DateTime $end, DaedalusInfo $daedalusInfo): int
    {
        $localizationConfig = $daedalusInfo->getLocalizationConfig();
        $daedalusConfig = $daedalusInfo->getGameConfig()->getDaedalusConfig();
        $start = clone $start;
        $end = clone $end;

        /** @var non-empty-string $timeZone */
        $timeZone = $localizationConfig->getTimeZone();
        $end->setTimezone(new \DateTimeZone($timeZone));
        $start->setTimezone(new \DateTimeZone($timeZone));

        $differencesInMinutes = $this->getDateIntervalAsMinutes($start, $end);

        return (int) floor($differencesInMinutes / $daedalusConfig->getCycleLength());
    }

    public function getExplorationDateStartNextCycle(Exploration $exploration): \DateTime
    {
        if (($dateExplorationLastCycle = $exploration->getUpdatedAt()) === null) {
            throw new \LogicException('Exploration should have an UpdatedAt Value');
        }

        $nextCycleStartAt = clone $dateExplorationLastCycle;

        return $nextCycleStartAt->add(new \DateInterval('PT' . $exploration->getCycleLength() . 'M'));
    }

    private function handleDaedalusCycleChange(\DateTime $dateTime, Daedalus $daedalus): int
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();
        $daedalusConfig = $daedalusInfo->getGameConfig()->getDaedalusConfig();

        if (!\in_array($daedalusInfo->getGameStatus(), [GameStatusEnum::STARTING, GameStatusEnum::CURRENT], true)) {
            return 0;
        }

        $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
        if ($dateDaedalusLastCycle === null) {
            throw new \LogicException('Daedalus should have a CycleStartedAt Value');
        }
        $dateDaedalusLastCycle = clone $dateDaedalusLastCycle;

        $cycleElapsed = $this->getNumberOfCycleElapsed($dateDaedalusLastCycle, $dateTime, $daedalusInfo);

        if ($cycleElapsed > 0) {
            $this->activateCycleChange($daedalus);

            try {
                $this->entityManager->beginTransaction();
                for ($i = 0; $i < $cycleElapsed; ++$i) {
                    $dateDaedalusLastCycle->add(new \DateInterval('PT' . $daedalusConfig->getCycleLength() . 'M'));
                    $cycleEvent = new DaedalusCycleEvent(
                        $daedalus,
                        [EventEnum::NEW_CYCLE],
                        $dateDaedalusLastCycle
                    );
                    $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

                    // Do not continue make cycle if Daedalus is finished
                    if ($daedalusInfo->getGameStatus() === GameStatusEnum::FINISHED) {
                        break;
                    }
                }

                $daedalus->setCycleStartedAt($dateDaedalusLastCycle);
                $this->deactivateCycleChange($daedalus);
                $this->entityManager->commit();
            } catch (\Throwable $error) {
                $this->logger->error('Error during cycle change', [
                    'daedalus' => $daedalus->getId(),
                    'error' => $error->getMessage(),
                    'trace' => $error->getTraceAsString(),
                ]);
                $this->entityManager->rollback();
                $this->deactivateCycleChange($daedalus);
                $this->entityManager->close();

                throw $error;
            }
        }

        return $cycleElapsed;
    }

    private function handleExplorationCycleChange(\DateTime $dateTime, Exploration $exploration): int
    {
        $closedExploration = $exploration->getClosedExploration();
        if ($this->isDaedalusOrExplorationFinished($closedExploration)) {
            return 0;
        }

        $dateExplorationLastCycle = $exploration->getUpdatedAt();
        if ($dateExplorationLastCycle === null) {
            throw new \LogicException('Exploration should have an UpdatedAt Value');
        }
        $dateExplorationLastCycle = clone $dateExplorationLastCycle;

        $cycleElapsed = $this->getNumberOfExplorationCycleElapsed($dateExplorationLastCycle, $dateTime, $exploration);

        if ($cycleElapsed > 0) {
            $this->activateExplorationCycleChange($exploration);

            try {
                $this->entityManager->beginTransaction();
                for ($i = 0; $i < $cycleElapsed; ++$i) {
                    $dateExplorationLastCycle->add(new \DateInterval('PT' . $exploration->getCycleLength() . 'M'));
                    $cycleEvent = new ExplorationEvent(
                        $exploration,
                        [EventEnum::NEW_CYCLE],
                        $dateExplorationLastCycle
                    );
                    $this->eventService->callEvent($cycleEvent, ExplorationEvent::EXPLORATION_NEW_CYCLE);

                    // Do not continue make cycle if Daedalus or exploration is finished
                    if ($this->isDaedalusOrExplorationFinished($closedExploration)) {
                        break;
                    }
                }
                $this->deactivateExplorationCycleChange($exploration);
                $this->entityManager->commit();
            } catch (\Throwable $e) {
                $this->logger->error('Error during exploration cycle change', [
                    'exploration' => $exploration->getId(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->entityManager->rollback();
                $this->deactivateExplorationCycleChange($exploration);
                $this->entityManager->close();
            }
        }

        return $cycleElapsed;
    }

    private function getNumberOfExplorationCycleElapsed(\DateTime $start, \DateTime $end, Exploration $exploration): int
    {
        $start = clone $start;
        $end = clone $end;

        /** @var non-empty-string $timeZone */
        $timeZone = $exploration->getDaedalus()->getDaedalusInfo()->getLocalizationConfig()->getTimeZone();
        $end->setTimezone(new \DateTimeZone($timeZone));
        $start->setTimezone(new \DateTimeZone($timeZone));

        $differencesInMinutes = $this->getDateIntervalAsMinutes($start, $end);

        return (int) floor($differencesInMinutes / $exploration->getCycleLength());
    }

    private function getDateIntervalAsMinutes(\DateTime $dateStart, \DateTime $dateEnd): int
    {
        $dateInterval = $dateEnd->diff($dateStart);

        return (int) $dateInterval->format('%a') * 24 * 60 +
                (int) $dateInterval->format('%H') * 60 +
                (int) $dateInterval->format('%i');
    }

    private function isDaedalusOrExplorationFinished(ClosedExploration $exploration): bool
    {
        $daedalusInfo = $exploration->getDaedalusInfo();

        return $daedalusInfo->isDaedalusFinished() || $exploration->isExplorationFinished();
    }

    private function activateCycleChange(Daedalus $daedalus): void
    {
        $daedalus->setIsCycleChange(true);
        $this->entityManager->persist($daedalus);
        $this->entityManager->flush();
    }

    private function deactivateCycleChange(Daedalus $daedalus): void
    {
        $daedalus->setIsCycleChange(false);
        $this->entityManager->persist($daedalus);
        $this->entityManager->flush();
    }

    private function activateExplorationCycleChange(Exploration $exploration): void
    {
        $exploration->setIsChangingCycle(true);
        $this->entityManager->persist($exploration);
        $this->entityManager->flush();
    }

    private function deactivateExplorationCycleChange(Exploration $exploration): void
    {
        $exploration->setIsChangingCycle(false);
        $this->entityManager->persist($exploration);
        $this->entityManager->flush();
    }
}

class CycleChangeResult
{
    public int $daedalusCyclesElapsed;
    public int $explorationCyclesElapsed;

    public function __construct(int $daedalusCyclesElapsed, int $explorationCyclesElapsed)
    {
        $this->daedalusCyclesElapsed = $daedalusCyclesElapsed;
        $this->explorationCyclesElapsed = $explorationCyclesElapsed;
    }

    public function noCycleElapsed(): bool
    {
        return $this->daedalusCyclesElapsed === 0 && $this->explorationCyclesElapsed === 0;
    }

    public function hasDaedalusCycleElapsed(): bool
    {
        return $this->daedalusCyclesElapsed > 0;
    }

    public function hasExplorationCycleElapsed(): bool
    {
        return $this->explorationCyclesElapsed > 0;
    }
}
