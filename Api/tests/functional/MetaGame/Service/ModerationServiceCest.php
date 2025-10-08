<?php

namespace Mush\Tests\functional\MetaGame\Service;

use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\BannedIp;

/**
 * @internal
 */
final class ModerationServiceCest extends AbstractFunctionalTest
{
    private ModerationServiceInterface $moderationService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->moderationService = $I->grabService(ModerationServiceInterface::class);
    }

    public function testBan(FunctionalTester $I): void
    {
        $user = $this->player1->getUser();
        $this->moderationService->banUser(
            user: $user,
            author: $this->player2->getUser(),
            reason: 'because',
            message: 'adminMessage',
            duration: new \DateInterval('P1D'),
        );

        $I->assertTrue($user->isBanned());
    }

    public function shouldBanByIp(FunctionalTester $I): void
    {
        $user = $this->player1->getUser();
        $hashedIp = hash_hmac('sha256', '127.0.0.1', 'my_secret');
        $user->addHashedIp($hashedIp);

        $this->moderationService->banUser(
            user: $user,
            author: $this->player2->getUser(),
            reason: 'because',
            message: 'adminMessage',
            duration: new \DateInterval('P1D'),
            byIp: true,
        );

        $I->assertTrue($user->isBanned());
        $I->seeInRepository(
            entity: BannedIp::class,
            params: [
                'value' => $hashedIp,
            ]
        );
    }
}
