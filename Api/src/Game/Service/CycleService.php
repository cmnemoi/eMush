<?php

namespace Mush\Game\Service;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Event\Service\EventServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;

class CycleService implements CycleServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService
    ) {
        $this->entityManager = $entityManager;
          $this->eventService = $eventService;
    }

    public function handleCycleChange(DateTime $dateTime, Daedalus $daedalus): int
    {
        $gameConfig = $daedalus->getGameConfig();

        if (!in_array($daedalus->getGameStatus(), [GameStatusEnum::STARTING, GameStatusEnum::CURRENT])) {
            return 0;
        }

        $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
        if ($dateDaedalusLastCycle === null) {
            throw new \LogicException('Daedalus should have a CycleStartedAt Value');
        } else {
            $dateDaedalusLastCycle = clone $dateDaedalusLastCycle;
        }

        $cycleElapsed = $this->getNumberOfCycleElapsed($dateDaedalusLastCycle, $dateTime, $gameConfig);

        if ($cycleElapsed > 0) {
            $daedalus->setIsCycleChange(true);
            $this->entityManager->persist($daedalus);
            $this->entityManager->flush();

            try {
                for ($i = 0; $i < $cycleElapsed; ++$i) {
                    $dateDaedalusLastCycle->add(new DateInterval('PT' . strval($gameConfig->getCycleLength()) . 'M'));
                    $cycleEvent = new DaedalusCycleEvent(
                        $daedalus,
                        EventEnum::NEW_CYCLE,
                        $dateDaedalusLastCycle
                    );
                    $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

                    // Do not continue make cycle if Daedalus is finish
                    if ($daedalus->getGameStatus() === GameStatusEnum::FINISHED) {
                        break;
                    }
                }
            } catch (\Exception $exception) {
            } finally {
                $daedalus->setCycleStartedAt($dateDaedalusLastCycle);
                $daedalus->setIsCycleChange(false);
                $this->entityManager->persist($daedalus);
                $this->entityManager->flush();
            }
        }

        return $cycleElapsed;
    }

    public function getDateStartNextCycle(Daedalus $daedalus): DateTime
    {
        $gameConfig = $daedalus->getGameConfig();

        if (($dateDaedalusLastCycle = $daedalus->getCycleStartedAt()) === null) {
            throw new \LogicException('Daedalus should have a CycleStartedAt Value');
        }

        $nextCycleStartAt = clone $dateDaedalusLastCycle;

        return $nextCycleStartAt->add(new DateInterval('PT' . strval($gameConfig->getCycleLength()) . 'M'));
    }

    // get day cycle from date (value between 1 and $gameConfig->getCyclePerGameDay())
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
     * First actual cycle of the ship (ie: for 3h cycle in fr, if the ship start C8, then it will be 21h:00).
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

        $gameDayLength = intval($gameConfig->getCyclePerGameDay() * $gameConfig->getCycleLength()); // in min
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
