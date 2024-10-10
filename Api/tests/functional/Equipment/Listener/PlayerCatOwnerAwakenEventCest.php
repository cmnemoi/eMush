<?php

namespace Mush\Tests\functional\Equipment\Listener;

use Mush\Equipment\Listener\PlayerEventSubscriber;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerCatOwnerAwakenEventCest extends AbstractFunctionalTest
{
    private $playerEventSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->playerEventSubscriber = $I->grabService(PlayerEventSubscriber::class);
    }

    public function ifRalucaAwakensSchrodingerShouldSpawn(FunctionalTester $I)
    {
        $I->markTestIncomplete();
    }

    public function ifSomeoneElseIsGivenCatOwnerStatusAndAwakensSchrodingerShouldSpawn(FunctionalTester $I)
    {
        $I->markTestIncomplete();
    }

    public function WhenSchrodingerIsSpawnedByACatOwnerThereShouldBeALog(FunctionalTester $I)
    {
        $I->markTestIncomplete();
    }
}
