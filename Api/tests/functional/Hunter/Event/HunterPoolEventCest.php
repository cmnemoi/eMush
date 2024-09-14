<?php

namespace Mush\Tests\functional\Hunter\Event;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HunterPoolEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testUnpoolHuntersEvent(FunctionalTester $I): void
    {
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $this->daedalus->setHunterPoints(40); // should be enough to unpool 4 hunters
        $I->assertCount(4, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testUnpoolHuntersEventWithExistingWave(FunctionalTester $I): void
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

    public function testUnpoolHuntersD1000DoesNotAppearBeforeDayTen(FunctionalTester $I): void
    {
        // given D1000s are the only hunters available
        $gameConfig = $this->daedalus->getGameConfig();
        $gameConfig->setHunterConfigs(
            $gameConfig->getHunterConfigs()->filter(static fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::DICE)
        );

        // given Daedalus is Day 9
        $this->daedalus->setDay(9);
        // given Daedalus has enough points to spawn D1000s
        $this->daedalus->setHunterPoints(100);

        // when we spawn hunters
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // then we should not have any attacking D1000s
        $I->assertEmpty($this->daedalus->getAttackingHunters());
    }

    public function testUnpoolHuntersD1000AppearAfterDayTen(FunctionalTester $I): void
    {
        // given D1000s are the only hunters available
        $gameConfig = $this->daedalus->getGameConfig();
        $gameConfig->setHunterConfigs(
            $gameConfig->getHunterConfigs()->filter(static fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::DICE)
        );

        // given Daedalus is Day 10
        $this->daedalus->setDay(10);
        // given Daedalus has enough points to spawn D1000s
        $this->daedalus->setHunterPoints(100);

        // when we spawn hunters
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // then we should have attacking D1000s
        $I->assertNotEmpty($this->daedalus->getAttackingHunters());
    }

    public function strateguruShouldReduceHuntersByThirtyThreePercent(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::STRATEGURU, $I);

        $this->daedalus->setHunterPoints(40); // should be enough to unpool 4 hunters

        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $I->assertCount(3, $this->daedalus->getAttackingHunters());
    }
}
