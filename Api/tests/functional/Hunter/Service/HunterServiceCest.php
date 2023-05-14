<?php

namespace functional\Hunter\Service;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Service\HunterService;
use Mush\Status\Enum\HunterStatusEnum;
use Symfony\Component\Uid\Uuid;

class HunterServiceCest extends AbstractFunctionalTest
{
    private HunterService $hunterService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->hunterService = $I->grabService(HunterService::class);
    }

    public function testUnpoolHunters(FunctionalTester $I)
    {
        $this->hunterService->unpoolHunters($this->daedalus, new \DateTime());
        $I->assertCount(4, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testMakeHuntersShoot(FunctionalTester $I)
    {
        $initialHull = $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull();
        $this->daedalus->setHunterPoints(100);
        $this->hunterService->unpoolHunters($this->daedalus, new \DateTime());

        // remove the truce status
        $hunters = $I->grabEntitiesFromRepository(Hunter::class);
        /** @var Hunter $hunter */
        foreach ($hunters as $hunter) {
            $status = $hunter->getStatusByName(HunterStatusEnum::HUNTER_CHARGE);
            $hunter->removeStatus($status);
        }

        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());
        $I->assertNotEquals($initialHull, $this->daedalus->getHull());
    }

    public function testMakeHuntersShootAsteroidFullHealth(FunctionalTester $I)
    {
        $daedalus = $this->createDaedalusForAsteroidTest($I);
        $daedalus->setHunterPoints(25);
        $this->hunterService->unpoolHunters($daedalus, new \DateTime());

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
                            ->getAttackingHunters()
                            ->filter(fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
                            ->first()
        ;
        $truceStatus = $asteroid->getStatusByName(HunterStatusEnum::HUNTER_CHARGE);
        $asteroid->removeStatus($truceStatus);

        $this->hunterService->makeHuntersShoot($daedalus->getAttackingHunters());

        $I->assertEquals(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull() - $asteroid->getHealth(),
            actual: $daedalus->getHull()
        );
        $I->assertIsEmpty($daedalus->getAttackingHunters()); // asteroid should be destroyed
    }

    public function testMakeHuntersShootAsteroidNotFullHealth(FunctionalTester $I)
    {
        $daedalus = $this->createDaedalusForAsteroidTest($I);
        $daedalus->setHunterPoints(25);
        $this->hunterService->unpoolHunters($daedalus, new \DateTime());

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
                            ->getAttackingHunters()
                            ->filter(fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
                            ->first()
        ;
        $truceStatus = $asteroid->getStatusByName(HunterStatusEnum::HUNTER_CHARGE);
        $asteroid->removeStatus($truceStatus);
        $asteroid->setHealth(1);

        $I->refreshEntities([$asteroid]);

        $this->hunterService->makeHuntersShoot($daedalus->getAttackingHunters());

        $I->assertEquals(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull() - $asteroid->getHealth(),
            actual: $daedalus->getHull()
        );
        $I->assertIsEmpty($daedalus->getAttackingHunters()); // asteroid should be destroyed
    }

    public function testMakeHuntersShootAsteroidNotDestroyedIfCantShoot(FunctionalTester $I)
    {
        $daedalus = $this->createDaedalusForAsteroidTest($I);
        $daedalus->setHunterPoints(25);
        $this->hunterService->unpoolHunters($daedalus, new \DateTime());

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
                            ->getAttackingHunters()
                            ->filter(fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
                            ->first()
        ;
        $I->assertNotFalse($asteroid);
        $I->assertNotNull($asteroid->getStatusByName(HunterStatusEnum::HUNTER_CHARGE));

        $this->hunterService->makeHuntersShoot($daedalus->getAttackingHunters());

        $I->assertEquals(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull(),
            actual: $daedalus->getHull()
        ); // asteroid should not deal damage
        $I->assertCount(1, $daedalus->getAttackingHunters()); // asteroid should not be destroyed
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
