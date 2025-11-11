<?php

namespace Mush\Tests\functional\MetaGame\Service;

use Mush\MetaGame\Repository\ModerationSanctionRepository;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\BannedIp;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class ModerationServiceCest extends AbstractFunctionalTest
{
    private ModerationServiceInterface $moderationService;
    private ModerationSanctionRepository $moderationSanctionRepository;

    private User $user;
    private User $author;
    private string $hashedIp;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->moderationService = $I->grabService(ModerationServiceInterface::class);
        $this->moderationSanctionRepository = $I->grabService(ModerationSanctionRepository::class);
        $this->user = $this->player1->getUser();
        $this->author = $this->player2->getUser();
        $this->hashedIp = hash_hmac('sha256', '127.0.0.1', 'my_secret');
    }

    public function shouldBanUserIfNotInGame(FunctionalTester $I): void
    {
        $this->user->stopGame();
        $this->whenBanUser();

        $this->thenUserIsBanned($I);
    }

    public function shouldNotBanUserIfInGame(FunctionalTester $I): void
    {
        $this->user->startGame();
        $this->whenBanUser();

        $this->thenUserIsNotBanned($I);
    }

    public function banShouldBeReplacedAfterAnotherBan(FunctionalTester $I): void
    {
        $dateInFiveHundredDays = new \DateTime()->add(new \DateInterval('P500D'));
        $dateInThreeDays = new \DateTime()->add(new \DateInterval('P3D'));
        // given user is not in game
        $this->user->stopGame();

        // given we ban user for 500 days
        $this->whenBanUser(new \DateInterval('P500D'));

        // then the active ban should end in 500 days
        $activeBan = $this->moderationSanctionRepository->findUserActiveBan($this->user);

        // then we have to set the first ban start date earlier as the database has a hardtime comparing times this close
        $activeBan->setStartDate(new \DateTime()->sub(new \DateInterval('PT1S')));
        $this->moderationSanctionRepository->save($activeBan);
        $I->assertEquals($dateInFiveHundredDays->format('Y-m-d-H-i'), $activeBan->getEndDate()->format('Y-m-d-H-i'));

        // given we ban user for 3 days this time
        $this->whenBanUser(new \DateInterval('P3D'));

        // then the active ban should end in 3 days
        $activeBan = $this->moderationSanctionRepository->findUserActiveBan($this->user);
        $I->assertEquals($dateInThreeDays->format('Y-m-d-H-i'), $activeBan->getEndDate()->format('Y-m-d-H-i'));
    }

    public function shouldBanUserByIp(FunctionalTester $I): void
    {
        $this->givenUserHasHashedIp();

        $this->whenBanUserByIp();

        $this->thenUserIsBanned($I);
        $this->thenIpIsBanned($I);
    }

    public function shouldNotSaveAlreadyBannedIp(FunctionalTester $I): void
    {
        $this->givenUserHasHashedIp();
        $this->givenIpIsAlreadyBanned($I);

        $this->whenBanUserByIp();

        $this->thenUserIsBanned($I);
        $this->thenOnlyOneBannedIpRecordExists($I);
    }

    private function givenUserHasHashedIp(): void
    {
        $this->user->addHashedIp($this->hashedIp);
    }

    private function givenIpIsAlreadyBanned(FunctionalTester $I): void
    {
        $bannedIp = new BannedIp($this->hashedIp);
        $I->haveInRepository($bannedIp);
    }

    private function whenBanUser(\DateInterval $duration = new \DateInterval('P1D')): void
    {
        $this->moderationService->banUser(
            user: $this->user,
            author: $this->author,
            reason: 'because',
            message: 'adminMessage',
            duration: $duration
        );
    }

    private function whenBanUserByIp(): void
    {
        $this->moderationService->banUser(
            user: $this->user,
            author: $this->author,
            reason: 'because',
            message: 'adminMessage',
            duration: new \DateInterval('P1D'),
            byIp: true,
        );
    }

    private function thenUserIsBanned(FunctionalTester $I): void
    {
        $I->assertTrue($this->user->isBanned());
    }

    private function thenUserIsNotBanned(FunctionalTester $I): void
    {
        $I->assertFalse($this->user->isBanned());
    }

    private function thenIpIsBanned(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: BannedIp::class,
            params: [
                'value' => $this->hashedIp,
            ]
        );
    }

    private function thenOnlyOneBannedIpRecordExists(FunctionalTester $I): void
    {
        $I->seeNumRecords(1, BannedIp::class, ['value' => $this->hashedIp]);
    }
}
