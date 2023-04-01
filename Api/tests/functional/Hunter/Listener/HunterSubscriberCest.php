<?php

namespace functional\Hunter\Listener;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Listener\HunterSubscriber;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;

class HunterSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private HunterSubscriber $hunterSubscriber;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->hunterSubscriber = $I->grabService(HunterSubscriber::class);
    }

    public function testOnHunterDeath(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, 1, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::POOL_HUNTERS);
        $unpoolEvent = new HunterPoolEvent($this->daedalus, 1, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $hunter = $this->daedalus->getAttackingHunters()->first();

        $hunterDeathEvent = new HunterEvent(
            $hunter,
            VisibilityEnum::PUBLIC,
            ['test'],
            new \DateTime()
        );
        $hunterDeathEvent->setAuthor($this->player1);
        $this->hunterSubscriber->onHunterDeath($hunterDeathEvent);

        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->seeInRepository(RoomLog::class,
            [
                'place' => $this->player1->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player1->getPlayerInfo(),
                'log' => LogEnum::HUNTER_DEATH,
                'visibility' => VisibilityEnum::PUBLIC,
            ]);
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
