<?php

namespace functional\Hunter\Listener;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;

class HunterSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testOnPoolHunters(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, 10, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::POOL_HUNTERS);
        $I->assertCount(0, $this->daedalus->getAttackingHunters());
        $I->assertCount(10, $this->daedalus->getHunterPool());
    }

    public function testOnUnpoolHunters(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, 10, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::POOL_HUNTERS);

        $unpoolEvent = new HunterPoolEvent($this->daedalus, 10, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $I->assertCount(10, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testOnUnpoolHuntersWithExistingWave(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, 10, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::POOL_HUNTERS);

        $unpoolEvent1 = new HunterPoolEvent($this->daedalus, 8, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent1, HunterPoolEvent::UNPOOL_HUNTERS);
        $unpoolEvent2 = new HunterPoolEvent($this->daedalus, 2, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent2, HunterPoolEvent::UNPOOL_HUNTERS);
        $I->assertCount(10, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testOnUnpoolHuntersWithNonEmptyPool(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, 10, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::POOL_HUNTERS);
        $unpoolEvent = new HunterPoolEvent($this->daedalus, 2, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $I->assertCount(2, $this->daedalus->getAttackingHunters());
        $I->assertCount(8, $this->daedalus->getHunterPool());
    }
}
