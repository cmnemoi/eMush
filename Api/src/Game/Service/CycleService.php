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

        $dateDaedalusLastCycle = clone $daedalus->getCycleStartedAt();

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
        $nextCycleStartAt = clone $daedalus->getCycleStartedAt();
        $nextCycleStartAt = $nextCycleStartAt->add(new DateInterval('PT' . strval($gameConfig->getCycleLength()) . 'M'));

        return $nextCycleStartAt;
    }

    //get day cycle from date (value between 1 and $gameConfig->getCyclePerGameDay())
    public function getInDayCycleFromDate(DateTime $date, GameConfig $gameConfig): int
    {
        $timeZoneDate = $date->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()));
        $minutes = intval($timeZoneDate->format('i'));
        $hours = intval($timeZoneDate->format('H'));

        return (int) (floor(
                    ($minutes + $hours * 60) / $gameConfig->getCycleLength() + 1
                ) - 1) % $gameConfig->getCyclePerGameDay() + 1;
    }

    /**
     * Get Daedalus first cycle date
     * First actual cycle of the ship (ie 3h cycle in fr, if the ship start C8, then it will be 21h:00).
     */
    public function getDaedalusStartingCycleDate(Daedalus $daedalus): DateTime
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
        $minutesBetweenDayStartAndDaedalusFirstCycle = $numberOfCompleteDay * $gameDayLength + (($daedalus->getCycle() - 1) * $gameConfig->getCycleLength());

        return $firstDayDate->add(new DateInterval('PT' . strval($minutesBetweenDayStartAndDaedalusFirstCycle) . 'M'));
    }

    private function getNumberOfCycleElapsed(DateTime $start, DateTime $end, GameConfig $gameConfig): int
    {
        $start = clone $start;
        $end = clone $end;
        $end->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()));
        $start->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()));

        $differencesInMinutes = $this->getDateIntervalAsMinutes($start, $end);

        return intval(floor($differencesInMinutes / $gameConfig->getCycleLength()));
    }

    private function getDateIntervalAsMinutes(DateTime $dateStart, DateTime $dateEnd): int
    {
        $dateInterval = $dateEnd->diff($dateStart);

        return intval($dateInterval->format('%a')) * 24 * 60 +
                intval($dateInterval->format('%H')) * 60 +
                intval($dateInterval->format('%i'));
    }
}
