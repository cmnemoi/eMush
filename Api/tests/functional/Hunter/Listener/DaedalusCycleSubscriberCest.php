<?php

namespace functional\Hunter\Listener;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;

class DaedalusCycleSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testMakeHunterShoot(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, 10, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::POOL_HUNTERS);
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $dateDaedalusLastCycle = $this->daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
        $cycleEvent = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $initHull = $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull();
        $I->assertNotEquals($initHull, $this->daedalus->getHull());
    }
}
