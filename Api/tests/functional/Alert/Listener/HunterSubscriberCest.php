<?php

namespace Mush\Tests\functional\Alert\Listener;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Listener\HunterSubscriber;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class HunterSubscriberCest extends AbstractFunctionalTest
{
    private HunterSubscriber $hunterSubscriber;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->hunterSubscriber = $I->grabService(HunterSubscriber::class);
    }

    public function testOnUnpoolHunter(FunctionalTester $I)
    {
        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->hunterSubscriber->onUnpoolHunters($event);

        $I->seeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::HUNTER]);
    }
}
