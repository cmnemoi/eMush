<?php

namespace Mush\RoomLog\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;

interface RoomLogRepositoryInterface
{
    /**
     * @psalm-suppress TooManyArguments
     */
    public function getPlayerRoomLog(Player $player): array;

    public function getAllRoomLogsByDaedalus(Daedalus $daedalus): array;

    public function findAllByDaedalusAndPlace(Daedalus $daedalus, Place $place): array;

    public function getOneBy(array $parameters): ?RoomLog;

    public function getBy(array $parameters): array;

    public function startTransaction(): void;

    public function save(RoomLog $roomLog): void;

    public function saveAll(array $roomLogs): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}
