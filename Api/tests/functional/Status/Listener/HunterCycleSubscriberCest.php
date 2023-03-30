<?php

namespace functional\Status\Listener;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Event\StatusCycleEvent;

class HunterCycleSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testOnNewCycle(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, 1, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::POOL_HUNTERS);
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $I->assertCount(1, $this->daedalus->getAttackingHunters());

        $attackingHunters = $this->daedalus->getAttackingHunters();
        dump($attackingHunters->first()->getStatuses());
        $hunterStatuses = $attackingHunters->map(fn ($hunter) => $hunter->getStatuses());

        dump($hunterStatuses);

        /** @var Status $status */
        foreach ($hunterStatuses as $status) {
            $statusNewCycle = new StatusCycleEvent(
                $status,
                $status->getOwner(),
                ['test'],
                new \DateTime()
            );
            $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }
    }
}
