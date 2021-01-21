<?php

namespace Mush\Game\Service;

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
        $cycleLength = $gameConfig->getCycleLength();
        $currentDate = new \DateTime();
        $lastUpdate = $daedalus->getUpdatedAt();
        $currentCycle = $daedalus->getCycle();
        $currentCycleStartedAt = clone $lastUpdate;
        $currentCycleStartedAt = $currentCycleStartedAt
            ->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()))
            ->setTime(($currentCycle - 1) * $cycleLength, 0, 0, 0)
            ->setTimezone(new \DateTimeZone('UTC'))
        ;

        $cycleElapsed = $this->getNumberOfCycleElapsed($lastUpdate, $currentDate, $gameConfig);

        $cycleInterval = new \DateInterval('PT' . $cycleLength . 'H');
        for ($i = 0; $i < $cycleElapsed; ++$i) {
            $currentCycleStartedAt->add($cycleInterval);
            $cycleEvent = new DaedalusCycleEvent($daedalus, $currentCycleStartedAt);
            $this->eventDispatcher->dispatch($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        }

        return $cycleElapsed;
    }

    public function getCycleFromDate(DateTime $date, GameConfig $gameConfig): int
    {
        $hour = intval($date->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()))->format('H'));

        return (int) floor(
            $hour / $gameConfig->getCycleLength() + 1
        );
    }

    public function getDateStartNextCycle(Daedalus $daedalus): DateTime
    {
        $currentCycle = $daedalus->getCycle();
        $gameConfig = $daedalus->getGameConfig();

        $currentCycleStartedAt = clone $daedalus->getUpdatedAt();
        $cycleLength = $gameConfig->getCycleLength();

        $cycleInterval = new \DateInterval('PT' . $cycleLength . 'H');

        return $currentCycleStartedAt
            ->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()))
            ->setTime(($currentCycle - 1) * $cycleLength, 0, 0, 0)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->add($cycleInterval)
        ;
    }

    private function getNumberOfCycleElapsed(DateTime $start, DateTime $end, GameConfig $gameConfig): int
    {
        $startCycle = $this->getCycleFromDate($start, $gameConfig);
        $endCycle = $this->getCycleFromDate($end, $gameConfig);

        $end->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()));
        $start->setTimezone(new \DateTimeZone($gameConfig->getTimeZone()));

        // We assume the inactivity is not more than a month
        if ($end->format('n') !== $start->format('n')) {
            $dayDifference = intval($start->format('t')) - intval($start->format('j')) + intval($end->format('j'));
        } else {
            $dayDifference = intval($end->format('j')) - intval($start->format('j'));
        }

        return intval($endCycle + $dayDifference * $gameConfig->getNumberOfCyclePerDay() - $startCycle);
    }
}
