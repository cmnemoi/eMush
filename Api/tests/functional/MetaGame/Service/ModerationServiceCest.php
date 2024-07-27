<?php

namespace Mush\Tests\functional\MetaGame\Service;

use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

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
}
