<?php

namespace Mush\Tests\functional\Hunter\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\DaedalusStatistics;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HunterDeathEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private Hunter $hunter;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        // given no hunters or scrap at the beginning of the test
        $I->assertEmpty($this->daedalus->getHuntersAroundDaedalus());
        $I->assertEmpty($this->daedalus->getSpace()->getEquipments());

        // given 1 hunter is spawn
        $this->daedalus->setHunterPoints(10); // should be enough to unpool 1 hunter
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $this->hunter = $this->daedalus->getHuntersAroundDaedalus()->first();
    }

    public function testHunterDeathEventPrintsHunterDeathLog(FunctionalTester $I): void
    {
        // given 1 hunter is attacking
        $I->assertCount(1, $this->daedalus->getHuntersAroundDaedalus());

        // when an event said this hunter is dead
        $hunterDeathEvent = new HunterEvent(
            $this->hunter,
            VisibilityEnum::PUBLIC,
            ['test', ActionEnum::SHOOT_HUNTER->value],
            new \DateTime()
        );
        $hunterDeathEvent->setAuthor($this->player1);
        $this->eventService->callEvent($hunterDeathEvent, HunterEvent::HUNTER_DEATH);

        // then there is a log about this hunter death
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player1->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player1->getPlayerInfo(),
                'log' => $hunterDeathEvent->mapLog(LogEnum::HUNTER_DEATH_LOG_ENUM),
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testHunterDeathEventDropsScrapInSpace(FunctionalTester $I): void
    {
        // given 1 hunter is attacking
        $I->assertCount(1, $this->daedalus->getHuntersAroundDaedalus());

        // when an event said this hunter is dead
        $hunterDeathEvent = new HunterEvent(
            $this->hunter,
            VisibilityEnum::PUBLIC,
            ['test', ActionEnum::SHOOT_HUNTER->value],
            new \DateTime()
        );
        $hunterDeathEvent->setAuthor($this->player1);
        $this->eventService->callEvent($hunterDeathEvent, HunterEvent::HUNTER_DEATH);

        // then the hunter dropped some scrap in the space
        $I->assertNotEmpty($this->daedalus->getSpace()->getEquipments()); // the hunter killed should drop some scrap
    }

    public function testShipsDestroyedCounterShouldIncrementWhenAHunterIsKilled(FunctionalTester $I): void
    {
        // given 1 hunter is attacking
        $I->assertCount(1, $this->daedalus->getHuntersAroundDaedalus());

        // given the ships destroyed counter is set to 0
        $this->daedalus->getDaedalusInfo()->setDaedalusStatistics(new DaedalusStatistics(shipsDestroyed: 0));

        // when an event said this hunter is dead
        $hunterDeathEvent = new HunterEvent(
            $this->hunter,
            VisibilityEnum::PUBLIC,
            ['test', ActionEnum::SHOOT_HUNTER->value],
            new \DateTime()
        );
        $hunterDeathEvent->setAuthor($this->player1);
        $this->eventService->callEvent($hunterDeathEvent, HunterEvent::HUNTER_DEATH);

        // then the ships destroyed counter should be incremented to 1.
        $I->assertEquals(1, $this->daedalus->getDaedalusInfo()->getDaedalusStatistics()->getShipsDestroyed(), 'shipsDestroyed should be 1.');
    }
}
