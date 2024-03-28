<?php

namespace Mush\Tests\functional\MetaGame\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Mush\User\Service\UserService;

class ModerationServiceCest extends AbstractFunctionalTest
{
    private ModerationServiceInterface $moderationService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->moderationService = $I->grabService(ModerationServiceInterface::class);
    }

    public function testBan(FunctionalTester $I)
    {
        $user = $this->player1->getUser();

        $this->moderationService->banUser($user, new \DateInterval('P1D'), 'because', 'adminMessage');

        $I->refreshEntities($user);

        $I->assertTrue($user->isBanned());
    }
}
