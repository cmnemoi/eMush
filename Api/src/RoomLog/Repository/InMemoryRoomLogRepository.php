<?php

namespace Mush\RoomLog\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\ValueObject\GameDate;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Skill\Enum\SkillEnum;

final class InMemoryRoomLogRepository implements RoomLogRepositoryInterface
{
    /** @var array<RoomLog> */
    private array $roomLogs = [];

    public function getPlayerRoomLog(Player $player): array
    {
        $daedalus = $player->getDaedalus();
        $daedalusDate = $daedalus->getGameDate();
        $numberOfCyclesToCheck = $this->getNumberOfCyclesToCheck($player);

        $logs = $this->filterRelevantLogsForPlayer($player, $daedalus, $daedalusDate, $numberOfCyclesToCheck);

        return $this->sortLogsByDateAndIdDescending($logs);
    }

    public function getAllRoomLogsByDaedalus(Daedalus $daedalus): array
    {
        $logs = [];
        foreach ($this->roomLogs as $roomLog) {
            if (
                $roomLog->getDaedalusInfo() === $daedalus->getDaedalusInfo()
            ) {
                $logs[] = $roomLog;
            }
        }

        return $logs;
    }

    public function findAllByDaedalusAndPlace(Daedalus $daedalus, Place $place): array
    {
        $logs = [];
        foreach ($this->roomLogs as $roomLog) {
            if (
                $roomLog->getDaedalusInfo() === $daedalus->getDaedalusInfo()
                && $roomLog->getPlace() === $place->getName()
            ) {
                $logs[] = $roomLog;
            }
        }

        return $logs;
    }

    public function startTransaction(): void
    {
        // No transaction in memory
    }

    public function save(RoomLog $roomLog): void
    {
        $id = crc32(serialize($roomLog));
        (new \ReflectionProperty($roomLog, 'id'))->setValue($roomLog, $id);
        $this->roomLogs[$roomLog->getId()] = $roomLog;
    }

    public function saveAll(array $roomLogs): void
    {
        foreach ($roomLogs as $roomLog) {
            $this->save($roomLog);
        }
    }

    public function commitTransaction(): void
    {
        // No transaction in memory
    }

    public function rollbackTransaction(): void
    {
        // No transaction in memory
    }

    public function clear(): void
    {
        $this->roomLogs = [];
    }

    public function getOneBy(array $parameters): ?RoomLog
    {
        foreach ($this->roomLogs as $roomLog) {
            if (
                $roomLog->getLog() === $parameters['log']
                && $roomLog->getDaedalusInfo() === $parameters['daedalusInfo']
                && $roomLog->getPlace() === $parameters['place']
                && $roomLog->getDay() === $parameters['day']
                && $roomLog->getCycle() === $parameters['cycle']
            ) {
                return $roomLog;
            }
        }

        return null;
    }

    public function getBy(array $parameters): array
    {
        $logs = [];
        foreach ($this->roomLogs as $roomLog) {
            if (
                $roomLog->getDaedalusInfo() === $parameters['daedalusInfo']
                && $roomLog->getPlace() === $parameters['place']
                && $roomLog->getDay() === $parameters['day']
                && $roomLog->getCycle() === $parameters['cycle']
            ) {
                $logs[] = $roomLog;
            }
        }

        return $logs;
    }

    public function findById(int $id): ?RoomLog
    {
        return $this->roomLogs[$id] ?? null;
    }

    public function findByPlayerAndLogKey(Player $player, string $logKey): ?RoomLog
    {
        foreach ($this->roomLogs as $roomLog) {
            if (
                $roomLog->getPlayerInfo() === $player->getPlayerInfo()
                && $roomLog->getLog() === $logKey
            ) {
                return $roomLog;
            }
        }

        return null;
    }

    public function findAllByPlayerAndLogKey(Player $player, string $logKey): RoomLogCollection
    {
        $logs = [];
        foreach ($this->roomLogs as $roomLog) {
            if (
                $roomLog->getPlayerInfo() === $player->getPlayerInfo()
                && $roomLog->getLog() === $logKey
            ) {
                $logs[] = $roomLog;
            }
        }

        return new RoomLogCollection($logs);
    }

    public function findOneByLogKey(string $logKey): ?RoomLog
    {
        foreach ($this->roomLogs as $roomLog) {
            if (
                $roomLog->getLog() === $logKey
            ) {
                return $roomLog;
            }
        }

        return null;
    }

    private function getNumberOfCyclesToCheck(Player $player): int
    {
        return $player->hasSkill(SkillEnum::TRACKER) ? 16 : 8;
    }

    private function filterRelevantLogsForPlayer(
        Player $player,
        Daedalus $daedalus,
        GameDate $daedalusDate,
        int $numberOfCyclesToCheck
    ): array {
        $logs = [];

        foreach ($this->roomLogs as $roomLog) {
            $roomLogDate = new GameDate($daedalus, $roomLog->getDay(), $roomLog->getCycle());
            if (
                $this->isLogInPlayerDaedalus($roomLog, $daedalus)
                && $this->isLogInPlayerPlace($roomLog, $player)
                && $this->isLogWithinTimeRange($roomLogDate, $daedalusDate, $numberOfCyclesToCheck)
                && $this->isLogVisibleToPlayer($roomLog, $player)
            ) {
                $logs[] = $roomLog;
            }
        }

        return $logs;
    }

    private function isLogInPlayerDaedalus(RoomLog $roomLog, Daedalus $daedalus): bool
    {
        return $roomLog->getDaedalusInfo() === $daedalus->getDaedalusInfo();
    }

    private function isLogInPlayerPlace(RoomLog $roomLog, Player $player): bool
    {
        return $roomLog->getPlace() === $player->getPlace()->getName();
    }

    private function isLogWithinTimeRange(GameDate $roomLogDate, GameDate $daedalusDate, int $numberOfCyclesToCheck): bool
    {
        return $daedalusDate->equals($roomLogDate)
            || $daedalusDate->cyclesAgo($numberOfCyclesToCheck)->lessThanOrEqual($roomLogDate);
    }

    private function isLogVisibleToPlayer(RoomLog $roomLog, Player $player): bool
    {
        return $roomLog->isPublicOrRevealed()
            || (
                $roomLog->getPlayerInfo() === $player->getPlayerInfo()
                && \in_array($roomLog->getVisibility(), [VisibilityEnum::PRIVATE, VisibilityEnum::SECRET, VisibilityEnum::COVERT], true)
            );
    }

    private function sortLogsByDateAndIdDescending(array $logs): array
    {
        usort($logs, static function ($a, $b) {
            $dateCompare = $b->getCreatedAt() <=> $a->getCreatedAt();
            if ($dateCompare === 0) {
                return $b->getId() <=> $a->getId();
            }

            return $dateCompare;
        });

        return $logs;
    }
}
