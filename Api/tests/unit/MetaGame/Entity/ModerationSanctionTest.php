<?php

namespace Mush\Tests\unit\MetaGame\Entity;

use Mush\MetaGame\Entity\ModerationSanction;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ModerationSanctionTest extends TestCase
{
    public function testIsSanctionActive()
    {
        $user = new User();
        $startSanction = new \DateTime('now');
        $startSanction->sub(new \DateInterval('P1D')); // sanction started yesterday

        $endSanction = new \DateTime('now');
        $endSanction->add(new \DateInterval('P1D')); // sanction end tomorrow

        $sanction = new ModerationSanction($user, $startSanction);

        // given sanction ends tomorrow
        $sanction->setEndDate($endSanction);
        // then the sanction is active
        self::assertTrue($sanction->getIsActive());

        // given sanction ended yesterday
        $endSanction->sub(new \DateInterval('PT35H'));
        $sanction->setEndDate($endSanction);
        // then the sanction is not active
        self::assertFalse($sanction->getIsActive());
    }
}
