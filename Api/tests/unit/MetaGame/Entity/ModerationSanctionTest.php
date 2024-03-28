<?php

namespace Mush\Tests\unit\MetaGame\Entity;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class ModerationSanctionTest extends TestCase
{
    public function testIsSanctionActive()
    {
        $user = new User();
        $startSanction = new \DateTime('now');
        $startSanction->sub(new \DateInterval('P1D')); // sanction started yesterday

        $endSanction = new \DateTime('now');
        $endSanction->add(new \DateInterval('P1D')); // sanction end tomorrow

        $sanction = new ModerationSanction($user, $startSanction);

        // given no end date is given, sanction is permanent
        $this->assertTrue($sanction->isSanctionActive());

        // given sanction ends tomorrow
        $sanction->setEndDate($endSanction);
        // then the sanction is active
        $this->assertTrue($sanction->isSanctionActive());


        // given sanction ended yesterday
        $endSanction->sub(new \DateInterval('PT35H'));
        $sanction->setEndDate($endSanction);
        // then the sanction is not active
        $this->assertFalse($sanction->isSanctionActive());
    }
}
