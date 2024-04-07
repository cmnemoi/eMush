<?php

namespace Mush\Tests\functional\MetaGame\Service;

use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class ModerationServiceCest extends AbstractFunctionalTest
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
        $this->moderationService->banUser($user, new \DateInterval('P1D'), 'because', 'adminMessage');

        $I->refreshEntities($user);
        $I->assertTrue($user->isBanned());
    }
}
