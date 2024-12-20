<?php

namespace Mush\RoomLog\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;

final class InMemoryRoomLogRepository implements RoomLogRepositoryInterface
{
    /** @var array<RoomLog> */
    private array $roomLogs = [];

    public function getPlayerRoomLog(PlayerInfo $playerInfo, \DateTime $limitDate = new \DateTime('1 day ago')): array
    {
        $player = $playerInfo->getPlayer();
        $logs = [];

        foreach ($this->roomLogs as $roomLog) {
            if (
                $roomLog->getDaedalusInfo() === $player?->getDaedalus()->getDaedalusInfo()
                && $roomLog->getPlace() === $player?->getPlace()->getName()
                && $roomLog->getCreatedAt() >= $limitDate
                && (
                    $roomLog->isPublicOrRevealed()
                    || (
                        $roomLog->getPlayerInfo() === $playerInfo
                        && \in_array($roomLog->getVisibility(), [VisibilityEnum::PRIVATE, VisibilityEnum::SECRET, VisibilityEnum::COVERT], true)
                    )
                )
            ) {
                $logs[] = $roomLog;
            }
        }

        // Sort by created date desc and id desc
        usort($logs, static function ($a, $b) {
            $dateCompare = $b->getCreatedAt() <=> $a->getCreatedAt();
            if ($dateCompare === 0) {
                return $b->getId() <=> $a->getId();
            }

            return $dateCompare;
        });

        return $logs;
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

        $this->roomLogs[$id] = $roomLog;
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
}
