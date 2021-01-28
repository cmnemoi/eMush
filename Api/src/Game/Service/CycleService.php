<?php

namespace Mush\Game\Service;

use DateInterval;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Entity\GameConfig;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CycleService implements CycleServiceInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleCycleChange(DateTime $dateTime, Daedalus $daedalus): int
    {
        $gameConfig = $daedalus->getGameConfig();

        $dateDaedalusLastCycle = $this->getDaedalusCurrentCycleDate($daedalus);

        $cycleElapsed = $this->getNumberOfCycleElapsed($dateDaedalusLastCycle, $dateTime, $gameConfig);

        for ($i = 0; $i < $cycleElapsed; ++$i) {
            $lastUpdateCycle = $dateDaedalusLastCycle->add(new DateInterval('PT' . strval($gameConfig->getCycleLength()) . 'M'));
            $cycleEvent = new DaedalusCycleEvent($daedalus, $lastUpdateCycle);
            $this->eventDispatcher->dispatch($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        }

        return $cycleElapsed;
    }

    public function getDateStartNextCycle(Daedalus $daedalus): DateTime
    {
        $gameConfig = $daedalus->getGameConfig();
        $elapsedCycles = $daedalus->getCycle() - 1 + ($daedalus->getDay() - 1) * $gameConfig->getCyclePerGameDay();
        $firstCycleDate = $this->getDaedalusStartingCycleDate($daedalus);

        return $this->getCycleDateFromStartingDate($elapsedCycles + 1, $firstCycleDate, $gameConfig);
    }

    public function getCycleFromDate(DateTime $date, GameConfig $gameConfig): int
    {
        $date = $date->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()));
        $midnight = clone $date;
        $midnight->setTime(0,0);
        $minutesSinceMidnight = $this->getDateIntervalAsMinutes($date, $midnight);

        $cycles = (int) floor($minutesSinceMidnight / ($gameConfig->getCycleLength()));

        return (($cycles) % $gameConfig->getCyclePerGameDay()) +1 ;
    }

    /**
     * Get the start of the daedalus current cycle.
     */
    private function getDaedalusCurrentCycleDate(Daedalus $daedalus): DateTime
    {
        $gameConfig = $daedalus->getGameConfig();
        $elapsedCycles = $daedalus->getCycle() - 1 + ($daedalus->getDay() - 1) * $gameConfig->getCyclePerGameDay();
        $daedalusDateFirstCycle = $this->getDaedalusStartingCycleDate($daedalus);

        return $this->getCycleDateFromStartingDate($elapsedCycles, $daedalusDateFirstCycle, $gameConfig);
    }

    /**
     * Get the starting date of the $elapsedCycles cycle from the $firstCycleDate.
     */
    private function getCycleDateFromStartingDate(int $elapsedCycles, DateTime $firstCycleDate, GameConfig $gameConfig): DateTime
    {
        $elapsedTimeInMinutes = intval(($elapsedCycles) * $gameConfig->getCycleLength());

        return $firstCycleDate->add(new DateInterval('PT' . strval($elapsedTimeInMinutes) . 'M'));
    }

    /**
     * Get Daedalus first cycle date 
     * Take 00h00 in the current Timezone as a reference and look how many complete days have already been completed
     * First cycle of the ship is the date of the begining of the started game day
     */
    private function getDaedalusStartingCycleDate(Daedalus $daedalus): DateTime
    {
        $gameConfig = $daedalus->getGameConfig();
        $firstCycleDate = $daedalus->getCreatedAt() ?? new DateTime();

        $firstDayDate = clone $firstCycleDate;
        $firstDayDate
            ->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()))
            ->setTime(0, 0, 0, 0)
            ->setTimezone(new \DateTimeZone('UTC'))
        ;

        $gameDayLength = intval($gameConfig->getCyclePerGameDay() * $gameConfig->getCycleLength()); //in min
        $numberOfCompleteDay = intval($this->getDateIntervalAsMinutes($firstCycleDate, $firstDayDate) / $gameDayLength);

        return $firstDayDate->add(new DateInterval('PT' . strval($numberOfCompleteDay * $gameDayLength) . 'M'));
    }

    private function getNumberOfCycleElapsed(DateTime $start, DateTime $end, GameConfig $gameConfig): int
    {
        $end->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()));
        $start->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()));

        $differencesInMinutes = $this->getDateIntervalAsMinutes($start, $end);

        return intval(floor($differencesInMinutes / $gameConfig->getCycleLength()));
    }

    private function getDateIntervalAsMinutes(DateTime $dateStart, DateTime $dateEnd): int
    {
        $dateInterval = $dateEnd->diff($dateStart);

        return intval($dateInterval->format('%a')) * 24 * 60 +
                intval($dateInterval->format('%h')) * 60 +
                intval($dateInterval->format('%m'));
    }
}
