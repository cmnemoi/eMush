<?php

namespace Mush\Tests\functional\Hunter\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusCycleSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testMakeHunterShoot(FunctionalTester $I)
    {
        $this->daedalus->setHunterPoints(10); // spawn a single hunter
        $poolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHitChance(100); // make sure it hits to avoid false negative tests

        // make hunter targeting Daedalus
        $target = new HunterTarget($hunter);
        $target->setTargetEntity($this->daedalus);
        $hunter->setTarget($target);
        $I->haveInRepository($hunter);

        $dateDaedalusLastCycle = $this->daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . (string) $this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() . 'M'));
        $cycleEvent = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $initHull = $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull();
        $I->assertNotEquals($initHull, $this->daedalus->getHull());
    }

    public function testHunterCannotShootTwoConsecutiveCycles(FunctionalTester $I)
    {
        $this->daedalus->setHunterPoints(10); // spawn a single hunter
        $poolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHitChance(100); // make sure it hits to avoid false negative tests
        $I->haveInRepository($hunter);

        $dateDaedalusLastCycle = $this->daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . (string) $this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() . 'M'));
        $cycleEvent = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $initHull = $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull();
        $I->assertEquals($initHull, $this->daedalus->getHull());

        $hullBeforeCycleChange = $this->daedalus->getHull();

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHitChance(100); // make sure it hits to avoid false negative tests
        $I->haveInRepository($hunter);

        $dateDaedalusLastCycle = $this->daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . (string) $this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() . 'M'));
        $cycleEvent = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertNotEquals($hullBeforeCycleChange, $this->daedalus->getHull());

        $hullBeforeCycleChange = $this->daedalus->getHull();

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHitChance(100); // make sure it hits to avoid false negative tests
        $I->haveInRepository($hunter);

        $dateDaedalusLastCycle = $this->daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . (string) $this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() . 'M'));
        $cycleEvent = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $hullBeforeCycleChange = $this->daedalus->getHull();

        $I->assertEquals($hullBeforeCycleChange, $this->daedalus->getHull());

        $hullBeforeCycleChange = $this->daedalus->getHull();

        $dateDaedalusLastCycle = $this->daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . (string) $this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() . 'M'));
        $cycleEvent = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertNotEquals($hullBeforeCycleChange, $this->daedalus->getHull());
    }

    public function testAsteroidTruceCycles(FunctionalTester $I): void
    {
        $daedalus = $this->getDaedalusForAsteroidTest($I);
        $daedalus->setHunterPoints(25);
        $poolEvent = new HunterPoolEvent($daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
            ->getAttackingHunters()
            ->filter(static fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
            ->first();
        $I->assertNotFalse($asteroid);
        $truceStatus = $asteroid->getStatusByName(HunterStatusEnum::ASTEROID_TRUCE_CYCLES);

        /** @var ChargeStatusConfig $truceStatusConfig */
        $truceStatusConfig = $truceStatus->getStatusConfig();
        $I->assertNotNull($asteroid->getStatusByName(HunterStatusEnum::ASTEROID_TRUCE_CYCLES));

        $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . (string) $daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() . 'M'));
        $cycleEvent = new DaedalusCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // asteroid should not have shot
        $initHull = $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull();
        $I->assertEquals($initHull, $daedalus->getHull());
        $I->assertEquals($truceStatusConfig->getStartCharge() - 1, $truceStatus->getCharge());
    }

    public function testAsteroidShootAfterDefinedCycles(FunctionalTester $I): void
    {
        $daedalus = $this->getDaedalusForAsteroidTest($I);
        $daedalus->setHunterPoints(25);
        $poolEvent = new HunterPoolEvent($daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
            ->getAttackingHunters()
            ->filter(static fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
            ->first();
        $truceStatus = $asteroid->getStatusByName(HunterStatusEnum::ASTEROID_TRUCE_CYCLES);

        /** @var ChargeStatusConfig $truceStatusConfig */
        $truceStatusConfig = $truceStatus?->getStatusConfig();

        for ($i = 0; $i < $truceStatusConfig->getStartCharge(); ++$i) {
            $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
            $dateDaedalusLastCycle->add(new \DateInterval('PT' . (string) $daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() . 'M'));
            $cycleEvent = new HunterCycleEvent(
                $daedalus,
                [EventEnum::NEW_CYCLE],
                $dateDaedalusLastCycle
            );
            $this->eventService->callEvent($cycleEvent, HunterCycleEvent::HUNTER_NEW_CYCLE);
        }

        // asteroid should have shot
        $initHull = $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull();
        $I->assertEquals($initHull - $asteroid->getHealth(), $daedalus->getHull());
    }

    public function testMakeHuntersShootD1000ActsThreeTimesACycleOnTwoConsecutiveCycles(FunctionalTester $I): void
    {
        // given D1000 is spawned
        $daedalus = $this->getDaedalusForD1000Test($I);
        $daedalus->setHunterPoints(30);
        $poolEvent = new HunterPoolEvent($daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // given D1000 has no truce status, 100% hit chance and 6 damage
        /** @var Hunter $d1000 */
        $d1000 = $daedalus
            ->getAttackingHunters()
            ->filter(static fn ($hunter) => $hunter->getName() === HunterEnum::DICE)
            ->first();
        $d1000->getHunterConfig()->setHitChance(100)->setDamageRange([6 => 1]);
        $d1000->setHitChance(100);

        $hunterTarget = new HunterTarget($d1000);
        $hunterTarget->setTargetEntity($daedalus);
        $d1000->setTarget($hunterTarget);

        // when hunter shoots
        $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . (string) $daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() . 'M'));
        $cycleEvent = new HunterCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, HunterCycleEvent::HUNTER_NEW_CYCLE);

        // then daedalus hull is damaged twice over the three actions
        // first time, d1000 has a target so it shots
        // second time, d1000 has no target so it does not shoot
        // third time, d1000 has a target so it shots
        $I->assertEquals(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull() - 6 * 2,
            actual: $daedalus->getHull(),
        );

        // when hunter shoots
        $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . (string) $daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() . 'M'));
        $cycleEvent = new HunterCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, HunterCycleEvent::HUNTER_NEW_CYCLE);

        // then daedalus hull is damaged once over the three actions
        // first time, d1000 has no target so it does not shoot
        // second time, d1000 has a target so it shots
        // third time, d1000 has no target so it does not shoot
        $I->assertEquals(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull() - 6 * 3,
            actual: $daedalus->getHull(),
        );
    }

    private function getDaedalusForAsteroidTest(FunctionalTester $I): Daedalus
    {
        /** @var Daedalus $daedalus */
        $daedalus = $this->daedalus;
        $daedalus
            ->setDay(5) // so asteroid can spawn
            ->setCycle(0);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        // only asteroids can spawn
        $gameConfig->setHunterConfigs(
            $gameConfig->getHunterConfigs()->filter(static fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::ASTEROID)
        );

        return $daedalus;
    }

    private function getDaedalusForD1000Test(FunctionalTester $I): Daedalus
    {
        /** @var Daedalus $daedalus */
        $daedalus = $this->daedalus;
        $daedalus
            ->setDay(10) // so D1000 can spawn
            ->setCycle(0);

        $I->haveInRepository($daedalus);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        // only D1000 can spawn
        $gameConfig->setHunterConfigs(
            $gameConfig->getHunterConfigs()->filter(static fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::DICE)
        );

        return $daedalus;
    }
}
