<?php

namespace Mush\Tests\functional\Hunter\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Listener\HunterSubscriber;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

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
        $space = $this->daedalus->getSpace();

        // no hunters, scrap or hunter killed at the beginning of the test
        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->assertEmpty($space->getEquipments());
        $I->assertEquals(0, $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getNumberOfHuntersKilled());

        $this->daedalus->setHunterPoints(10); // should be enough to unpool 1 hunter
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $hunter = $this->daedalus->getAttackingHunters()->first();

        $hunterDeathEvent = new HunterEvent(
            $hunter,
            VisibilityEnum::PUBLIC,
            ['test', ActionEnum::SHOOT_HUNTER],
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
                'log' => $hunterDeathEvent->mapLog(LogEnum::HUNTER_DEATH_LOG_ENUM),
                'visibility' => VisibilityEnum::PUBLIC,
            ]);
        $I->assertNotEmpty($space->getEquipments()); // the hunter killed should drop some scrap
        $I->assertEquals(1, $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getNumberOfHuntersKilled());
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
