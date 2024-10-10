<?php

namespace Mush\Tests\functional\Player\Event;

use Mush\Player\Listener\EquipmentSubscriber;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerReactsToSchrodingerDeathCest extends AbstractFunctionalTest
{
    private $equipmentSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->equipmentSubscriber = $I->grabService(EquipmentSubscriber::class);
    }

    public function ifSchrodingerDiesRalucaShouldLose4Morale(FunctionalTester $I)
    {
        $I->markTestIncomplete();
    }

    public function ifSomeoneElseIsGivenCatOwnerStatusAndSchrodingerDiesTheyShouldLose4Morale(FunctionalTester $I)
    {
        $I->markTestIncomplete();
    }
}
