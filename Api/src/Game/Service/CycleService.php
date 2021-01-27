<?php

namespace Mush\Game\Service;

use DateInterval;
use DateTime;
use Error;
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

    /**
     * @Warning: To be reworked, doesn't work with hours changes
     */
    public function handleCycleChange(Daedalus $daedalus): int
    {
        $gameConfig = $daedalus->getGameConfig();

        $currentDate = new DateTime();
        $lastUpdate = $daedalus->getUpdatedAt();

        $cycleElapsed = $this->getNumberOfCycleElapsed($lastUpdate, $currentDate, $gameConfig);

        $lastUpdateCycle = $this->getDateCurrentCycleFromDaedalus($daedalus);

        for ($i = 0; $i < $cycleElapsed; ++$i) {
            $lastUpdateCycle = $lastUpdateCycle->add(new DateInterval('PT' . strval($gameConfig->getCycleLength()) . 'M'));
            $cycleEvent = new DaedalusCycleEvent($daedalus, $lastUpdateCycle);
            $this->eventDispatcher->dispatch($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        }

        return $cycleElapsed;
    }

    public function getCycleDateFromStartingDate(int $elapsedCycles, DateTime $firstCycleDate, GameConfig $gameConfig): DateTime
    {
        $elapsedMins = intval(($elapsedCycles) * $gameConfig->getCycleLength());

        return $firstCycleDate->add(new DateInterval('PT' . strval($elapsedMins) . 'M'));
    }

    public function getDateCurrentCycleFromDaedalus(Daedalus $daedalus): DateTime
    {
        $gameConfig = $daedalus->getGameConfig();
        $elapsedCycles = $daedalus->getCycle() + 1 + $daedalus->getDay() * $gameConfig->getCyclePerGameDay();
        $firstCycleDate = $this->getStartingCycleDate($daedalus);

        return $this->getCycleDateFromStartingDate($elapsedCycles, $firstCycleDate, $gameConfig);
    }

    public function getDateStartNextCycle(Daedalus $daedalus): DateTime
    {
        $gameConfig = $daedalus->getGameConfig();
        $elapsedCycles = $daedalus->getCycle() + 1 + $daedalus->getDay() * $gameConfig->getCyclePerGameDay();
        $firstCycleDate = $this->getStartingCycleDate($daedalus);

        return $this->getCycleDateFromStartingDate($elapsedCycles + 1, $firstCycleDate, $gameConfig);
    }

    public function getCycleFromDate(DateTime $currentDate, Daedalus $daedalus): int
    {
        $gameConfig = $daedalus->getGameConfig();
        if (floatval(24 * 60 / ($gameConfig->getCycleLength())) !==
        floatval(floor(24 * 60 / ($gameConfig->getCycleLength())))) {
            throw new Error('Cycle setting of GameConfig are invalid. CycleLength should divide the number of minutes in a day');
        }

        $currentDate = $currentDate->setTimezone(new \DateTimeZone('UTC'));

        $firstCycleDate = $this->getStartingCycleDate($daedalus);

        $durationCycles = $this->getNumberOfCycleElapsed($currentDate, $firstCycleDate, $gameConfig);
        $durationDays = $this->getGameDayFromDate($currentDate, $daedalus);

        return (int) ($durationCycles + 1 - ($durationDays - 1) * $gameConfig->getCyclePerGameDay());
    }

    public function getGameDayFromDate(DateTime $currentDate, Daedalus $daedalus): int
    {
        $currentDate = $currentDate->setTimezone(new \DateTimeZone('UTC'));

        $gameConfig = $daedalus->getGameConfig();

        $firstCycleDate = $this->getStartingCycleDate($daedalus);

        $gameDayLength = intval($gameConfig->getCyclePerGameDay() * $gameConfig->getCycleLength()); //in min

        return (int) floor($this->getDateIntervalAsMinutes(date_diff($currentDate, $firstCycleDate)) / $gameDayLength + 1);
    }

    public function getStartingCycleDate(Daedalus $daedalus): DateTime
    {
        $gameConfig = $daedalus->getGameConfig();
        $firstCycleDate = $daedalus->getCreatedAt();

        $firstDayDate = clone $firstCycleDate;
        $firstDayDate
            ->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()))
            ->setTime(0, 0, 0, 0)
            ->setTimezone(new \DateTimeZone('UTC'))
        ;

        $gameDayLength = intval($gameConfig->getCyclePerGameDay() * $gameConfig->getCycleLength()); //in min

        $numberOfCompleteDay = intval($this->getDateIntervalAsMinutes(date_diff($firstCycleDate, $firstDayDate)) / $gameDayLength);

        return $firstDayDate->add(new DateInterval('PT' . strval($numberOfCompleteDay * $gameDayLength) . 'M'));
    }

    private function getNumberOfCycleElapsed(DateTime $start, DateTime $end, GameConfig $gameConfig): int
    {
        $dateInterval = date_diff($end, $start);

        return intval(floor($this->getDateIntervalAsMinutes($dateInterval) / $gameConfig->getCycleLength()));
    }

    private function getDateIntervalAsMinutes(DateInterval $dateInterval): int
    {
        return intval($dateInterval->format('%a')) * 24 * 60 +
                intval($dateInterval->format('%h')) * 60 +
                intval($dateInterval->format('%m'));
    }
}
