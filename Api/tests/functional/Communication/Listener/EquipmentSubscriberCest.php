<?php

namespace Mush\Tests\functional\Communication\Listener;

use Mush\RoomLog\Listener\EquipmentSubscriber;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class EquipmentSubscriberCest extends AbstractFunctionalTest
{
    private $equipmentSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->equipmentSubscriber = $I->grabService(EquipmentSubscriber::class);
    }

    public function ifSchrodingerGetsDestroyedNeronShouldSpeak(FunctionalTester $I)
    {
        $I->markTestIncomplete();
    }
}
