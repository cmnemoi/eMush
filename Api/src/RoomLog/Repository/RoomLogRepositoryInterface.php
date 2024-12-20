<?php

namespace Mush\RoomLog\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;

interface RoomLogRepositoryInterface
{
    /**
     * @psalm-suppress TooManyArguments
     */
    public function getPlayerRoomLog(PlayerInfo $playerInfo, \DateTime $limitDate = new \DateTime('1 day ago')): array;

    public function getAllRoomLogsByDaedalus(Daedalus $daedalus): array;

    public function findAllByDaedalusAndPlace(Daedalus $daedalus, Place $place): array;

    public function getOneBy(array $parameters): ?RoomLog;

    public function getBy(array $parameters): array;

    public function startTransaction(): void;

    public function save(RoomLog $roomLog): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}
