<?php

namespace Mush\Tests\functional\MetaGame\Service;

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

    private User $user;
    private User $author;
    private string $hashedIp;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->moderationService = $I->grabService(ModerationServiceInterface::class);
        $this->user = $this->player1->getUser();
        $this->author = $this->player2->getUser();
        $this->hashedIp = hash_hmac('sha256', '127.0.0.1', 'my_secret');
    }

    public function shouldBanUser(FunctionalTester $I): void
    {
        $this->whenBanUser();

        $this->thenUserIsBanned($I);
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

    private function whenBanUser(): void
    {
        $this->moderationService->banUser(
            user: $this->user,
            author: $this->author,
            reason: 'because',
            message: 'adminMessage',
            duration: new \DateInterval('P1D'),
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
