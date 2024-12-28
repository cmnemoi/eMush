<?php

declare(strict_types=1);

namespace Mush\RoomLog\Service;

use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Repository\InMemoryRoomLogRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class HideRoomLogsServiceTest extends TestCase
{
    private InMemoryRoomLogRepository $roomLogRepository;
    private HideRoomLogsService $hideRoomLogsService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->roomLogRepository = new InMemoryRoomLogRepository();
        $this->hideRoomLogsService = new HideRoomLogsService($this->roomLogRepository);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->roomLogRepository->clear();
    }

    public function testShouldHideLogs(): void
    {
        $roomLogs = $this->givenRoomLogs(number: 5);

        $this->whenIHideTheLogs($roomLogs);

        $this->thenLogsShouldBeHidden($roomLogs);
    }

    private function givenRoomLogs(int $number): RoomLogCollection
    {
        $roomLogs = new RoomLogCollection();
        for ($i = 0; $i < $number; ++$i) {
            $roomLog = new RoomLog();
            $roomLog->setLog("log {$i}");
            $this->roomLogRepository->save($roomLog);
            $roomLogs->add($roomLog);
        }

        return $roomLogs;
    }

    private function whenIHideTheLogs(RoomLogCollection $roomLogs): void
    {
        $this->hideRoomLogsService->execute($roomLogs);
    }

    private function thenLogsShouldBeHidden(RoomLogCollection $roomLogs): void
    {
        foreach ($roomLogs as $roomLog) {
            $savedLog = $this->roomLogRepository->findById($roomLog->getId());
            self::assertTrue($savedLog->isHidden());
        }
    }
}
