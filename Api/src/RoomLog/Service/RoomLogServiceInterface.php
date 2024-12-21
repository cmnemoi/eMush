<?php

namespace Mush\RoomLog\Service;

use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\ValueObject\DaedalusDate;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;

interface RoomLogServiceInterface
{
    public function createLog(
        string $logKey,
        Place $place,
        string $visibility,
        string $type,
        ?Player $player = null,
        array $parameters = [],
        ?\DateTime $dateTime = null
    ): RoomLog;

    public function createLogFromActionEvent(ActionEvent $event): ?RoomLog;

    public function persist(RoomLog $roomLog): RoomLog;

    public function getRoomLog(Player $player): RoomLogCollection;

    public function findAllByDaedalusAndPlace(Daedalus $daedalus, Place $place): RoomLogCollection;

    public function getDaedalusRoomLogs(Daedalus $daedalus): RoomLogCollection;

    public function getNumberOfUnreadRoomLogsForPlayer(Player $player): int;

    public function markRoomLogAsReadForPlayer(RoomLog $roomLog, Player $player): void;

    public function markAllRoomLogsAsReadForPlayer(Player $player): void;

    public function findOneByPlaceAndDaedalusDateOrThrow(string $logKey, Place $place, DaedalusDate $date): RoomLog;

    public function findAllByPlaceAndDaedalusDate(Place $place, DaedalusDate $date): RoomLogCollection;
}
