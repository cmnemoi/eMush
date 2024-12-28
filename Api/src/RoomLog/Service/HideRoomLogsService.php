<?php

declare(strict_types=1);

namespace Mush\RoomLog\Service;

use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Repository\RoomLogRepositoryInterface;

final class HideRoomLogsService
{
    public function __construct(
        private RoomLogRepositoryInterface $roomLogRepository
    ) {}

    public function execute(RoomLogCollection $roomLogs): void
    {
        $roomLogs = $roomLogs->filter(static fn (RoomLog $roomLog) => $roomLog->isNotHidden());
        $roomLogs->map(static fn (RoomLog $roomLog) => $roomLog->hide());

        $this->roomLogRepository->saveAll($roomLogs->toArray());
    }
}
