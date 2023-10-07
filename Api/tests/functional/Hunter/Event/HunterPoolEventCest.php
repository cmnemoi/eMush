<?php

namespace Mush\Tests\functional\Hunter\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class HunterSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testUnpoolHuntersEvent(FunctionalTester $I)
    {
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $this->daedalus->setHunterPoints(40); // should be enough to unpool 4 hunters
        $I->assertCount(4, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testUnpoolHuntersEventWithExistingWave(FunctionalTester $I)
    {
        $this->daedalus->setHunterPoints(40); // should be enough to unpool 4 hunters
        $unpoolEvent1 = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent1, HunterPoolEvent::UNPOOL_HUNTERS);
        $this->daedalus->setHunterPoints(40); // should be enough to unpool 4 hunters
        $unpoolEvent2 = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent2, HunterPoolEvent::UNPOOL_HUNTERS);
        $I->assertCount(8, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testUnpoolHuntersCreatesAHunterAlert(FunctionalTester $I): void
    {
        // when we spawn hunters
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // then we should have a hunter alert
        $I->seeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::HUNTER]);
    }
}
