<?php

namespace Mush\Tests\unit\User\Entity;

use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserNotBannedOtherSanction()
    {
        $user = new User();
        $startSanction = new \DateTime('now');
        $startSanction->sub(new \DateInterval('P1D')); // sanction started yesterday

        $endSanction = new \DateTime('now');
        $endSanction->add(new \DateInterval('P1D')); // sanction end tomorrow

        $sanction = new ModerationSanction($user, $startSanction);
        $sanction->setModerationAction(ModerationSanctionEnum::DELETE_MESSAGE)->setEndDate($endSanction);

        $user->addModerationSanction($sanction);

        // the sanction is not a ban
        $this->assertFalse($user->isBanned());
    }

    public function testIsUserBannedTemporary()
    {
        $user = new User();
        $startSanction = new \DateTime('now');
        $startSanction->sub(new \DateInterval('P1D')); // sanction started yesterday

        $endSanction = new \DateTime('now');
        $endSanction->add(new \DateInterval('P1D')); // sanction end tomorrow

        $sanction = new ModerationSanction($user, $startSanction);
        $sanction->setModerationAction(ModerationSanctionEnum::BAN_USER);

        $user->addModerationSanction($sanction);

        // given sanction ends tomorrow
        $sanction->setEndDate($endSanction);
        // then the sanction is active
        $this->assertTrue($user->isBanned());
    }
}
