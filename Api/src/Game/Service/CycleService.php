<?php

namespace Mush\Game\Service;

use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Event\CycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CycleService implements CycleServiceInterface
{
    private GameConfig $gameConfig;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * CycleService constructor.
     * @param GameConfigServiceInterface $gameConfigService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        GameConfigServiceInterface $gameConfigService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gameConfig = $gameConfigService->getConfig();
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @Warning: To be reworked, doesn't work with hours changes
     * @param Daedalus $daedalus
     * @return int
     */
    public function handleCycleChange(Daedalus $daedalus): int
    {
        $currentDate = new \DateTime();
        $lastUpdate = $daedalus->getUpdatedAt();
        $currentCycle = $daedalus->getCycle() % $this->gameConfig->getNumberOfCyclePerDay();
        $currentCycleStartedAt = clone $lastUpdate;
        $currentCycleStartedAt = $currentCycleStartedAt
            ->setTimezone(new \DateTimeZone($this->gameConfig->getTimeZone()))
            ->setTime(($currentCycle - 1) * $this->gameConfig->getCycleLength(), 0, 0, 0)
        ;

        $cycleElapsed = $this->getNumberOfCycleElapsed($lastUpdate, $currentDate);

        for ($i = 0; $i < $cycleElapsed; $i++) {
            $cycleEvent = new CycleEvent($daedalus, $currentCycleStartedAt);
            $this->eventDispatcher->dispatch($cycleEvent, CycleEvent::NEW_CYCLE);
        }

        return $cycleElapsed;
    }

    public function getCycleFromDate(DateTime $date): int
    {
        $hour = $date->setTimezone(new \DateTimeZone($this->gameConfig->getTimeZone()))->format('H');
        return floor(
            $hour / $this->gameConfig->getCycleLength() + 1
        );
    }

    private function getNumberOfCycleElapsed(DateTime $start, DateTime $end): int
    {
        $startCycle = $this->getCycleFromDate($start);
        $endCycle = $this->getCycleFromDate($end);

        $end->setTimezone(new \DateTimeZone($this->gameConfig->getTimeZone()));
        $start->setTimezone(new \DateTimeZone($this->gameConfig->getTimeZone()));

        // We assume the inactivity is not more than a month
        if ($end->format('n') !== $start->format('n')) {
            $dayDifference = $start->format('t') - $start->format('j') + $end->format('j');
        } else {
            $dayDifference = $end->format('j') - $start->format('j');
        }

        return $endCycle + $dayDifference * $this->gameConfig->getNumberOfCyclePerDay() - $startCycle;
    }
}
