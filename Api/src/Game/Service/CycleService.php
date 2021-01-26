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

    /**
     * @Warning: To be reworked, doesn't work with hours changes
     */
    public function handleCycleChange(Daedalus $daedalus): int
    {
        $gameConfig = $daedalus->getGameConfig();

        $currentDate = DateTime::createFromFormat('U', time());
        $lastUpdate = $daedalus->getUpdatedAt();

        $cycleElapsed = $this->getNumberOfCycleElapsed($lastUpdate, $currentDate, $gameConfig);

        $lastUpdateCycle = $this->getDateFromDaedalus($daedalus->getDay(), $daedalus->getCycle(), $daedalus);

        for ($i = 0; $i < $cycleElapsed; ++$i) {
            $date = $lastUpdateCycle->add(new DateInterval('PT' . strval($i * $gameConfig->getCycleLength()) . 'M'));
            $cycleEvent = new DaedalusCycleEvent($daedalus, $date);
            $this->eventDispatcher->dispatch($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        }

        return $cycleElapsed;
    }

    public function getDateFromDaedalus(int $day, int $cycle, Daedalus $daedalus): DateTime
    {
        $gameConfig = $daedalus->getGameConfig();

        $durationCycles = $cycle + $day * $gameConfig->getCyclePerGameDay();
        $durationMins = intval(($durationCycles) * $gameConfig->getCycleLength());

        $cycle0Date = $this->getStartingCycleDate($daedalus);

        return $cycle0Date->add(new DateInterval('PT' . strval($durationMins) . 'M'));
    }

    public function getCycleFromDate(DateTime $date, Daedalus $daedalus): int
    {
        $date = $date->setTimezone(new \DateTimeZone('UTC'));

        $gameConfig = $daedalus->getGameConfig();
        $cycle0Date = $this->getStartingCycleDate($daedalus);

        $durationCycles = $this->getNumberOfCycleElapsed($date, $cycle0Date, $gameConfig);
        $durationDays = $this->getGameDayFromDate($date, $daedalus);

        return (int) ($durationCycles + 1 - ($durationDays - 1) * $gameConfig->getCyclePerGameDay());
    }

    public function getGameDayFromDate(DateTime $date, Daedalus $daedalus): int
    {
        $date = $date->setTimezone(new \DateTimeZone('UTC'));

        $gameConfig = $daedalus->getGameConfig();

        $cycle0Date = $this->getStartingCycleDate($daedalus);

        $gameDayLength = intval($gameConfig->getCyclePerGameDay() * $gameConfig->getCycleLength()); //in min

        return (int) floor($this->getDateIntervalAsMinutes(date_diff($date, $cycle0Date)) / $gameDayLength + 1);
    }

    public function getStartingCycleDate(Daedalus $daedalus): DateTime
    {
        $gameConfig = $daedalus->getGameConfig();
        $cycle0Date = $daedalus->getCreatedAt();

        $day0Date = clone $cycle0Date;
        $day0Date
            ->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()))
            ->setTime(0, 0, 0, 0)
            ->setTimezone(new \DateTimeZone('UTC'))
        ;

        $gameDayLength = intval($gameConfig->getCyclePerGameDay() * $gameConfig->getCycleLength()); //in min

        $numberOfCompleteDay = intval($this->getDateIntervalAsMinutes(date_diff($cycle0Date, $day0Date)) / $gameDayLength);

        return $day0Date->add(new DateInterval('PT' . strval($numberOfCompleteDay * $gameDayLength) . 'M'));
    }

    public function getDateStartNextCycle(Daedalus $daedalus): DateTime
    {
        return $this->getDateFromDaedalus($daedalus->getDay(), $daedalus->getCycle() + 1, $daedalus)
        ;
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
