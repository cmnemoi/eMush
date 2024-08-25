<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TrackerCest extends AbstractFunctionalTest
{
    private AddSkillToPlayerService $addSkillToPlayer;
    private RoomLogService $roomLogService;

    private RoomLog $log;
    private RoomLogCollection $chunLogs;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->roomLogService = $I->grabService(RoomLogService::class);

        $this->givenChunIsATracker();
    }

    public function shouldSeeLogsFromTwoDaysAgo(FunctionalTester $I): void
    {
        $this->givenALogFromTwoDaysAgo();

        $this->whenChunReadsTheLogs();

        $this->thenChunShouldBeAbleToSeeTheLog($I);
    }

    private function givenALogFromTwoDaysAgo(): void
    {
        $this->log = $this->roomLogService->createLog(
            logKey: LogEnum::AWAKEN,
            place: $this->chun->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $this->chun,
            dateTime: new \DateTime('-2 days'),
        );
    }

    private function whenChunReadsTheLogs(): void
    {
        $this->chunLogs = $this->roomLogService->getRoomLog($this->chun);
    }

    private function thenChunShouldBeAbleToSeeTheLog(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->chunLogs->filter(fn (RoomLog $log) => $log->getId() === $this->log->getId()));
    }

    private function givenChunIsATracker(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::TRACKER, $this->chun);
    }
}
