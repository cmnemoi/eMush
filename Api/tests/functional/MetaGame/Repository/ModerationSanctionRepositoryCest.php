<?php

declare(strict_types=1);

namespace Mush\tests\functional\MetaGame\Repository;

use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Repository\ModerationSanctionRepository;
use Mush\MetaGame\Service\ModerationService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ModerationSanctionRepositoryCest extends AbstractFunctionalTest
{
    private ModerationSanctionRepository $moderationSanctionRepository;

    private ModerationService $moderationService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->moderationSanctionRepository = $I->grabService(ModerationSanctionRepository::class);

        $this->moderationService = $I->grabService(ModerationService::class);
    }

    public function shouldReturnUserActiveBansAndWarnings(FunctionalTester $I): void
    {
        $now = new \DateTime();
        $oneDayLater = (clone $now)->add(new \DateInterval('P1D'));

        // given Chun's user is warned
        $this->moderationService->warnUser(
            user: $this->chun->getUser(),
            author: $this->kuanTi->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'hello, world!',
        );

        // given Chun's user is banned
        $this->moderationService->banUser(
            user: $this->chun->getUser(),
            author: $this->kuanTi->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'hello, world!',
        );

        // given Chun is put in quarantine
        $this->moderationService->quarantinePlayer(
            player: $this->chun,
            author: $this->kuanTi->getUser(),
            reason: 'flood',
            message: 'hello, world!',
        );

        // when I get the user sanctions from the repository
        $sanctions = $this->moderationSanctionRepository->findAllUserActiveBansAndWarnings($this->chun->getUser());

        // then I should see the user sanction
        $I->assertCount(2, $sanctions);
        $I->assertEquals(ModerationSanctionEnum::WARNING, $sanctions[0]->getModerationAction());
        $I->assertEquals('flood', $sanctions[0]->getReason());
        $I->assertEquals('hello, world!', $sanctions[0]->getMessage());
        $I->assertEquals($now->format('Y-m-d-H-i'), $sanctions[0]->getStartDate()->format('Y-m-d-H-i'));
        $I->assertEquals($oneDayLater->format('Y-m-d-H-i'), $sanctions[0]->getEndDate()->format('Y-m-d-H-i'));
    }

    public function shouldNotReturnOtherUserActiveBansAndWarnings(FunctionalTester $I): void
    {
        $now = new \DateTime();

        // given KT's user is warned
        $this->moderationService->warnUser(
            user: $this->kuanTi->getUser(),
            author: $this->kuanTi->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'hello, world!',
        );

        // when I get Chun's sanctions from the repository
        $sanctions = $this->moderationSanctionRepository->findAllUserActiveBansAndWarnings($this->chun->getUser());

        // then I should not see any sanction
        $I->assertCount(0, $sanctions);
    }

    public function shouldNotReturnUserInActiveBansAndWarnings(FunctionalTester $I): void
    {
        // given we have a warning for chun
        $sanction = $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::WARNING,
            reason: 'flood',
            message: 'hello, world!',
            duration: new \DateInterval('PT1S'),
        );

        // given the warning ended yesterday
        $sanction->setStartDate(new \DateTime('yesterday 14:00'));
        $sanction->setEndDate(new \DateTime('yesterday 15:00'));
        $this->moderationSanctionRepository->save($sanction);

        // when I get the user sanctions from the repository
        $sanctions = $this->moderationSanctionRepository->findAllUserActiveBansAndWarnings($this->chun->getUser());

        // then I should not see the user sanction
        $I->assertCount(0, $sanctions);
    }

    public function shouldReturnUserActiveWarnings(FunctionalTester $I): void
    {
        // given Chun's user has an active warning
        $this->moderationService->warnUser(
            user: $this->chun->getUser(),
            author: $this->kuanTi->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'hello, world!',
        );

        // when I get the user warnings from the repository
        $warnings = $this->moderationSanctionRepository->findUserAllActiveWarnings($this->chun->getUser());

        // then I should see the warning
        $I->assertNotEmpty($warnings->toArray());
        $I->assertCount(1, $warnings->toArray());
    }

    public function shouldNotReturnExpiredWarningsInFindUserAllActiveWarnings(FunctionalTester $I): void
    {
        // given Chun has an expired warning
        $sanction = $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::WARNING,
            reason: 'flood',
            message: 'hello, world!',
            duration: new \DateInterval('PT1S'),
        );

        // given the warning ended yesterday
        $sanction->setStartDate(new \DateTime('yesterday 14:00'));
        $sanction->setEndDate(new \DateTime('yesterday 15:00'));
        $this->moderationSanctionRepository->save($sanction);

        // when I get the user warnings from the repository
        $warnings = $this->moderationSanctionRepository->findUserAllActiveWarnings($this->chun->getUser());

        // then I should not see the warning
        $I->assertEmpty($warnings->toArray());
    }

    public function shouldNotReturnOtherUserWarningsInFindUserAllActiveWarnings(FunctionalTester $I): void
    {
        // given KT's user has a warning
        $this->moderationService->warnUser(
            user: $this->kuanTi->getUser(),
            author: $this->kuanTi->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'hello, world!',
        );

        // when I get Chun's warnings from the repository
        $warnings = $this->moderationSanctionRepository->findUserAllActiveWarnings($this->chun->getUser());

        // then I should not see any warning
        $I->assertEmpty($warnings->toArray());
    }

    public function shouldReturnUserActiveBan(FunctionalTester $I): void
    {
        $now = new \DateTime();
        $oneDayLater = (clone $now)->add(new \DateInterval('P1D'));

        // given Chun's user is banned
        $this->moderationService->banUser(
            user: $this->chun->getUser(),
            author: $this->kuanTi->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'hello, world!',
        );

        // when I get the user ban from the repository
        $ban = $this->moderationSanctionRepository->findUserActiveBan($this->chun->getUser());

        // then I should see the ban
        $I->assertNotNull($ban);
        $I->assertEquals(ModerationSanctionEnum::BAN_USER, $ban->getModerationAction());
        $I->assertEquals('flood', $ban->getReason());
        $I->assertEquals('hello, world!', $ban->getMessage());
        $I->assertEquals($now->format('Y-m-d-H-i'), $ban->getStartDate()->format('Y-m-d-H-i'));
        $I->assertEquals($oneDayLater->format('Y-m-d-H-i'), $ban->getEndDate()->format('Y-m-d-H-i'));
    }

    public function shouldReturnNullWhenNoActiveBan(FunctionalTester $I): void
    {
        // when I get the user ban from the repository
        $ban = $this->moderationSanctionRepository->findUserActiveBan($this->chun->getUser());

        // then I should not see any ban
        $I->assertNull($ban);
    }

    public function shouldReturnNullWhenBanIsExpired(FunctionalTester $I): void
    {
        // given Chun has an expired ban
        $sanction = $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::BAN_USER,
            reason: 'flood',
            message: 'hello, world!',
            duration: new \DateInterval('PT1S'),
        );

        // given the ban ended yesterday
        $sanction->setStartDate(new \DateTime('yesterday 14:00'));
        $sanction->setEndDate(new \DateTime('yesterday 15:00'));
        $this->moderationSanctionRepository->save($sanction);

        // when I get the user ban from the repository
        $ban = $this->moderationSanctionRepository->findUserActiveBan($this->chun->getUser());

        // then I should not see the ban
        $I->assertNull($ban);
    }

    public function shouldReturnMostRecentBanWhenMultipleActiveBans(FunctionalTester $I): void
    {
        // given Chun has two active bans
        $this->moderationService->banUser(
            user: $this->chun->getUser(),
            author: $this->kuanTi->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'first ban',
        );

        // make the first ban older by setting its start date to yesterday
        $firstBan = $this->moderationSanctionRepository->findUserActiveBan($this->chun->getUser());
        $firstBan->setStartDate(new \DateTime('yesterday'));
        $this->moderationSanctionRepository->save($firstBan);

        $this->moderationService->banUser(
            user: $this->chun->getUser(),
            author: $this->kuanTi->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'hate_speech',
            message: 'second ban',
        );

        // when I get the user ban from the repository
        $ban = $this->moderationSanctionRepository->findUserActiveBan($this->chun->getUser());

        // then I should see the most recent ban
        $I->assertNotNull($ban);
        $I->assertEquals('hate_speech', $ban->getReason());
        $I->assertEquals('second ban', $ban->getMessage());
    }

    public function shouldReturnAllPendingBansForUser(FunctionalTester $I): void
    {
        // given Chun has a pending ban
        $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::BAN_USER_PENDING,
            reason: 'flood',
            message: 'hello, world!',
            duration: new \DateInterval('P1D'),
        );

        // when I get all pending bans for the user
        $pendingBans = $this->moderationSanctionRepository->findAllBansNotYetTriggeredForUser($this->chun->getUser());

        // then I should see the pending ban
        $I->assertNotEmpty($pendingBans->toArray());
        $I->assertCount(1, $pendingBans->toArray());
        $I->assertEquals(ModerationSanctionEnum::BAN_USER_PENDING, $pendingBans->toArray()[0]->getModerationAction());
    }

    public function shouldNotReturnOtherUserPendingBans(FunctionalTester $I): void
    {
        // given KT has a pending ban
        $this->moderationService->addSanctionEntity(
            $this->kuanTi->getUser(),
            $this->kuanTi->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::BAN_USER_PENDING,
            reason: 'flood',
            message: 'hello, world!',
            duration: new \DateInterval('P1D'),
        );

        // when I get all pending bans for Chun
        $pendingBans = $this->moderationSanctionRepository->findAllBansNotYetTriggeredForUser($this->chun->getUser());

        // then I should not see any pending ban
        $I->assertEmpty($pendingBans->toArray());
    }

    public function shouldReturnAllPendingBansForAllUsers(FunctionalTester $I): void
    {
        // given Chun has a pending ban
        $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::BAN_USER_PENDING,
            reason: 'flood',
            message: 'first pending ban',
            duration: new \DateInterval('P1D'),
        );

        // given KT has a pending ban
        $this->moderationService->addSanctionEntity(
            $this->kuanTi->getUser(),
            $this->kuanTi->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::BAN_USER_PENDING,
            reason: 'hate_speech',
            message: 'second pending ban',
            duration: new \DateInterval('P1D'),
        );

        // when I get all pending bans
        $pendingBans = $this->moderationSanctionRepository->findAllBansNotYetTriggeredForAll();

        // then I should see both pending bans
        $I->assertNotEmpty($pendingBans->toArray());
        $I->assertCount(2, $pendingBans->toArray());
    }

    public function shouldReturnEmptyCollectionWhenNoPendingBans(FunctionalTester $I): void
    {
        // when I get all pending bans
        $pendingBans = $this->moderationSanctionRepository->findAllBansNotYetTriggeredForAll();

        // then I should not see any pending ban
        $I->assertEmpty($pendingBans->toArray());
    }

    public function shouldReturnAllPlayerReports(FunctionalTester $I): void
    {
        // given Chun has a report
        $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::REPORT,
            reason: 'flood',
            message: 'hello, world!',
            duration: new \DateInterval('P1D'),
        );

        // when I get all reports for Chun
        $reports = $this->moderationSanctionRepository->findAllPlayerReport($this->chun->getPlayerInfo());

        // then I should see the report
        $I->assertNotEmpty($reports);
        $I->assertCount(1, $reports);
        $I->assertEquals(ModerationSanctionEnum::REPORT, $reports[0]->getModerationAction());
    }

    public function shouldReturnAllPlayerReportTypes(FunctionalTester $I): void
    {
        // given Chun has various report types
        $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::REPORT,
            reason: 'flood',
            message: 'report',
            duration: new \DateInterval('P1D'),
        );

        $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::REPORT_ABUSIVE,
            reason: 'flood',
            message: 'report_abusive',
            duration: new \DateInterval('P1D'),
        );

        $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::REPORT_PROCESSED,
            reason: 'flood',
            message: 'report_processed',
            duration: new \DateInterval('P1D'),
        );

        // when I get all reports for Chun
        $reports = $this->moderationSanctionRepository->findAllPlayerReport($this->chun->getPlayerInfo());

        // then I should see all report types
        $I->assertCount(3, $reports);
        $reportActions = array_map(static fn ($report) => $report->getModerationAction(), $reports);
        $I->assertContains(ModerationSanctionEnum::REPORT, $reportActions);
        $I->assertContains(ModerationSanctionEnum::REPORT_ABUSIVE, $reportActions);
        $I->assertContains(ModerationSanctionEnum::REPORT_PROCESSED, $reportActions);
    }

    public function shouldNotReturnOtherPlayerReports(FunctionalTester $I): void
    {
        // given KT has a report
        $this->moderationService->addSanctionEntity(
            $this->kuanTi->getUser(),
            $this->kuanTi->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::REPORT,
            reason: 'flood',
            message: 'hello, world!',
            duration: new \DateInterval('P1D'),
        );

        // when I get all reports for Chun
        $reports = $this->moderationSanctionRepository->findAllPlayerReport($this->chun->getPlayerInfo());

        // then I should not see any report
        $I->assertEmpty($reports);
    }

    public function shouldSaveModerationSanction(FunctionalTester $I): void
    {
        // given I have a moderation sanction
        $sanction = $this->moderationService->addSanctionEntity(
            $this->chun->getUser(),
            $this->chun->getPlayerInfo(),
            $this->kuanTi->getUser(),
            ModerationSanctionEnum::WARNING,
            reason: 'flood',
            message: 'hello, world!',
            duration: new \DateInterval('P1D'),
        );

        // when I modify and save the sanction
        $sanction->setMessage('updated message');
        $this->moderationSanctionRepository->save($sanction);

        // then the sanction should be updated in the database
        $updatedSanction = $this->moderationSanctionRepository->findUserAllActiveWarnings($this->chun->getUser())->toArray()[0];
        $I->assertEquals('updated message', $updatedSanction['message']);
    }
}
