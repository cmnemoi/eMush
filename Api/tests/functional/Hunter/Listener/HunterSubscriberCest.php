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
        $this->daedalus->setHunterPoints(10); // should be enough to unpool 1 hunter
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
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

    public function testOnUnpoolHunters(FunctionalTester $I)
    {
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $this->daedalus->setHunterPoints(40); // should be enough to unpool 4 hunters
        $I->assertCount(4, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testOnUnpoolHuntersWithExistingWave(FunctionalTester $I)
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
}
