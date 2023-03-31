<?php

namespace functional\Status\Listener;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;

class HunterCycleSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testOnHunterCreation(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, 1, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::POOL_HUNTERS);
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $I->assertCount(1, $this->daedalus->getAttackingHunters());

        $I->seeInRepository(Status::class);
    }

    public function testOnNewCycle(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, 1, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::POOL_HUNTERS);
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $I->assertCount(1, $this->daedalus->getAttackingHunters());

        /** @var ChargeStatus $hunterStatus */
        $hunterStatus = $I->grabEntityFromRepository(Status::class);

        for ($i = 0; $i < $hunterStatus->getCharge(); ++$i) {
            $daedalusEvent = new DaedalusCycleEvent($this->daedalus, [], new \DateTime());
            $this->eventService->callEvent($daedalusEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        }

        $I->dontSeeInRepository(Status::class);
    }
}
