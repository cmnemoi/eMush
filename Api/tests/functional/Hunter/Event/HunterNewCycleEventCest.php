<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Hunter\Event;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class HunterNewCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private HunterServiceInterface $hunterService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->hunterService = $I->grabService(HunterServiceInterface::class);
    }

    public function testHuntersDoNotShootTheCycleAfterTheyAreSpawn(FunctionalTester $I): void
    {
        // given some hunters are spawn
        $this->daedalus->setHunterPoints(40);
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // given those hunters are aiming at the daedalus
        $this->daedalus->getAttackingHunters()->map(fn (Hunter $hunter) => $hunter->setTarget(new HunterTarget($hunter)));

        // given they have a 100% chance to hit
        $this->daedalus->getAttackingHunters()
                        ->map(fn (Hunter $hunter) => $hunter->setHitChance(100))
                        ->map(fn (Hunter $hunter) => $I->haveInRepository($hunter))
        ;

        $hunter = $this->daedalus->getAttackingHunters()->first();

        // given a travel starts
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // given a travel finishes
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);

        // delete attacking hunters from travel so we only study the ones from the pool next
        $this->hunterService->delete($this->daedalus->getAttackingHunters()->toArray());

        // given multiple cycles pass
        for ($i = 0; $i < 3; ++$i) {
            // do not spawn hunters during those cycles
            $this->daedalus->getGameConfig()->getDifficultyConfig()->setHunterSpawnRate(0);
            $daedalusEvent = new DaedalusEvent(
                daedalus: $this->daedalus,
                tags: [],
                time: new \DateTime()
            );
            $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DAEDALUS_NEW_CYCLE);
        }

        // given a (random) wave of hunters is spawn
        $this->daedalus->setHunterPoints(40);
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $daedalusHullBeforeCycleChange = $this->daedalus->getHull();

        // when a new cycle passes
        $daedalusEvent = new DaedalusEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DAEDALUS_NEW_CYCLE);

        // then the hunters should not shoot the daedalus, so the hull should not change
        $I->assertEquals($daedalusHullBeforeCycleChange, $this->daedalus->getHull());

        // when another cycle passes
        $daedalusEvent = new DaedalusEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DAEDALUS_NEW_CYCLE);

        // then the hunters should shoot the daedalus, so the hull should have changed
        // then hunters should have shot, so daedalus should have lost hull
        $I->assertLessThan(
            expected: $daedalusHullBeforeCycleChange,
            actual: $this->daedalus->getHull()
        );
    }
}
