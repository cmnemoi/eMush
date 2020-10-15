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
    public function __construct(GameConfigServiceInterface $gameConfigService, EventDispatcherInterface $eventDispatcher)
    {
        $this->gameConfig = $gameConfigService->getConfig();
        $this->eventDispatcher = $eventDispatcher;
    }


    public function handleCycleChange(Daedalus $daedalus): bool {
        $currentDate = new \DateTime();
        $lastUpdate = $daedalus->getUpdatedAt();
        $currentCycle = $daedalus->getCycle() % $this->gameConfig->getNumberOfCyclePerDay();
        $currentCycleStartedAt = $lastUpdate->setTimezone(new \DateTimeZone($this->gameConfig->getTimeZone()))->setTime(($currentCycle - 1) * $this->gameConfig->getCycleLength(),0,0,0);

        $cycleElapsed = $this->getNumberOfCycleElapsed($lastUpdate, $currentDate);

        for ($i = 0; $i < $cycleElapsed; $i++) {
            $cycleEvent = new CycleEvent($currentCycleStartedAt);
            $cycleEvent->setDaedalus($daedalus);
            $this->eventDispatcher->dispatch($cycleEvent,CycleEvent::NEW_CYCLE);
        }

        return $currentCycle !== 0;
    }

    public function getCycleFromDate(DateTime $date): int {
        return floor(
             $date->setTimezone(new \DateTimeZone($this->gameConfig->getTimeZone()))->format('H') / $this->gameConfig->getCycleLength() + 1
        );
    }

    private function getNumberOfCycleElapsed(DateTime $start, DateTime $end): int {
        $startCycle = $this->getCycleFromDate($start);
        $endCycle = $this->getCycleFromDate($end);

        $end->setTimezone(new \DateTimeZone($this->gameConfig->getTimeZone()));
        $start->setTimezone(new \DateTimeZone($this->gameConfig->getTimeZone()));

        $dayDifference = 0;
        // We assume the inactivity is not more than a month
        if (date('n',$end) !== date('n',$start)) {
            $dayDifference = date('t', $start) - date('j', $start) + date('j', $end);
        } else {
            $dayDifference = date('j', $endCycle)- date('j', $start);
        }

        return $endCycle + $dayDifference * $this->gameConfig->getNumberOfCyclePerDay() - $startCycle;
    }
}