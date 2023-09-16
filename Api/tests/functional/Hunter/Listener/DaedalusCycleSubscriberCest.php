<?php

namespace functional\Hunter\Listener;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\HunterStatusEnum;
use Symfony\Component\Uid\Uuid;

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
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
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
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
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
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
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
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
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
        $daedalus = $this->createDaedalusForAsteroidTest($I);
        $daedalus->setHunterPoints(25);
        $poolEvent = new HunterPoolEvent($daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
                            ->getAttackingHunters()
                            ->filter(fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
                            ->first()
        ;
        $I->assertNotFalse($asteroid);
        $truceStatus = $asteroid->getStatusByName(HunterStatusEnum::HUNTER_CHARGE);
        /** @var ChargeStatusConfig $truceStatusConfig */
        $truceStatusConfig = $truceStatus->getStatusConfig();
        $I->assertNotNull($asteroid->getStatusByName(HunterStatusEnum::HUNTER_CHARGE));

        $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
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
        $daedalus = $this->createDaedalusForAsteroidTest($I);
        $daedalus->setHunterPoints(25);
        $poolEvent = new HunterPoolEvent($daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
                            ->getAttackingHunters()
                            ->filter(fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
                            ->first()
        ;
        /** @var ChargeStatusConfig $truceStatusConfig */
        $truceStatusConfig = $asteroid->getStatusByName(HunterStatusEnum::HUNTER_CHARGE)->getStatusConfig();

        for ($i = 0; $i < $truceStatusConfig->getStartCharge() + 1; ++$i) {
            $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
            $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
            $cycleEvent = new DaedalusCycleEvent(
                $daedalus,
                [EventEnum::NEW_CYCLE],
                $dateDaedalusLastCycle
            );
            $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        }

        // asteroid should have shot
        $initHull = $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull();
        $I->assertEquals($initHull - $asteroid->getHealth(), $daedalus->getHull());
    }

    public function testMakeHuntersShootD1000ActsThreeTimesACycleOnTwoConsecutiveCycles(FunctionalTester $I): void
    {
        // given D1000 is spawned
        $daedalus = $this->createDaedalusForD1000Test($I);
        $daedalus->setHunterPoints(30);
        $poolEvent = new HunterPoolEvent($daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // given D1000 has no truce status, 100% hit chance and 6 damage
        /** @var Hunter $d1000 */
        $d1000 = $daedalus
                            ->getAttackingHunters()
                            ->filter(fn ($hunter) => $hunter->getName() === HunterEnum::DICE)
                            ->first()
        ;
        $d1000->getHunterConfig()->setHitChance(100)->setDamageRange([6 => 1]);
        $d1000->setHitChance(100);

        $hunterTarget = new HunterTarget($d1000);
        $hunterTarget->setTargetEntity($daedalus);
        $d1000->setTarget($hunterTarget);

        // when hunter shoots
        $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
        $cycleEvent = new HunterCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, HunterCycleEvent::HUNTER_NEW_CYCLE);

        // then daedalus hull is damaged twice over the three actions
        // first time, d100 has a target so it shots
        // second time, d1000 has no target so it does not shoot
        // third time, d1000 has no target so it does not shoot
        $I->assertEquals(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull() - 6 * 2,
            actual: $daedalus->getHull(),
        );

        // when hunter shoots
        $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
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

    private function createDaedalusForAsteroidTest(FunctionalTester $I): Daedalus
    {
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var Daedalus $daedalus */
        $daedalus = new Daedalus();
        $daedalus
            ->setDay(5) // so asteroid can spawn
            ->setCycle(0)
            ->setDaedalusVariables($daedalusConfig)
            ->setCycleStartedAt(new \DateTime())
        ;

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        // only asteroids can spawn
        $gameConfig->setHunterConfigs(
            $gameConfig->getHunterConfigs()->filter(fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::ASTEROID)
        );

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $neron = new Neron();
        $I->haveInRepository($neron);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setName(Uuid::v4()->toRfc4122())
            ->setNeron($neron)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        $I->refreshEntities($daedalusInfo);

        $places = $this->createPlaces($I, $daedalus);
        $daedalus->setPlaces($places);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $I->haveInRepository($daedalus);

        return $daedalus;
    }

    private function createDaedalusForD1000Test(FunctionalTester $I): Daedalus
    {
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var Daedalus $daedalus */
        $daedalus = new Daedalus();
        $daedalus
            ->setDay(10) // so D1000 can spawn
            ->setCycle(0)
            ->setDaedalusVariables($daedalusConfig)
            ->setCycleStartedAt(new \DateTime())
        ;

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        // only D1000 can spawn
        $gameConfig->setHunterConfigs(
            $gameConfig->getHunterConfigs()->filter(fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::DICE)
        );

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $neron = new Neron();
        $I->haveInRepository($neron);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setName(Uuid::v4()->toRfc4122())
            ->setNeron($neron)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        $I->haveInRepository($daedalusInfo);

        $places = $this->createPlaces($I, $daedalus);
        $daedalus->setPlaces($places);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $I->haveInRepository($daedalus);

        return $daedalus;
    }
}
