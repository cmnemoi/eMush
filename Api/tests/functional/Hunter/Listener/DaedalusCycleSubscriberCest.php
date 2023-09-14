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
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterPoolEvent;
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

        // remove truce status
        $hunter->removeStatus($hunter->getStatusByName(HunterStatusEnum::HUNTER_CHARGE));

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

        // remove truce status
        $hunter->removeStatus($hunter->getStatusByName(HunterStatusEnum::HUNTER_CHARGE));
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

        $I->assertEquals($hullBeforeCycleChange, $this->daedalus->getHull());

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

        $dateDaedalusLastCycle = $this->daedalus->getCycleStartedAt();
        $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength()) . 'M'));
        $cycleEvent = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            $dateDaedalusLastCycle
        );
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals($hullBeforeCycleChange, $this->daedalus->getHull());
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
        $I->assertNotNull($asteroid->getStatusByName(HunterStatusEnum::HUNTER_CHARGE));
        $I->assertEquals(6 + 1, $truceStatus->getCharge()); // 6 cycles of truce + 1 for its spawn

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
        $I->assertEquals(5 + 1, $truceStatus->getCharge()); // 5 cycles of truce + 1 for its spawn
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
}
