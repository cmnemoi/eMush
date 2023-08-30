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
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\Hunter\Service\HunterService;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\HunterStatusEnum;
use Symfony\Component\Uid\Uuid;

class HunterServiceCest extends AbstractFunctionalTest
{
    private HunterService $hunterService;
    private GameEquipment $pasiphae;
    private ChargeStatusConfig $pasiphaeArmorStatusConfig;
    private ChargeStatus $pasiphaeArmorStatus;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->hunterService = $I->grabService(HunterService::class);

        $pasiphaeRoom = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $this->pasiphae = new GameEquipment($pasiphaeRoom);
        $this->pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($this->pasiphae);

        $this->pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $this->pasiphaeArmorStatus = new ChargeStatus($this->pasiphae, $this->pasiphaeArmorStatusConfig);
        $I->haveInRepository($this->pasiphaeArmorStatus);

        $this->player2->setPlace($this->daedalus->getPlaceByName(RoomEnum::SPACE));
        $I->haveInRepository($this->player2);

        $this->daedalus->setHunterPoints(10); // spawn a single hunter
        $this->hunterService->unpoolHunters($this->daedalus, new \DateTime());
    }

    public function testUnpoolHunters(FunctionalTester $I)
    {
        $this->hunterService->unpoolHunters($this->daedalus, new \DateTime());
        $I->assertCount(1, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testMakeHuntersShootDaedalus(FunctionalTester $I)
    {
        $this->testMakeHuntersShootTarget($I, HunterTargetEnum::DAEDALUS);
        $I->assertNotEquals(
            expected: $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull(),
            actual: $this->daedalus->getHull()
        );
    }

    public function testMakeHuntersShootPatrolShip(FunctionalTester $I)
    {
        $this->testMakeHuntersShootTarget($I, HunterTargetEnum::PATROL_SHIP);
        $I->assertNotEquals(
            expected: $this->pasiphaeArmorStatusConfig->getStartCharge(),
            actual: $this->pasiphaeArmorStatus->getCharge()
        );
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

        $I->haveInRepository($asteroid);

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

    private function testMakeHuntersShootTarget(FunctionalTester $I, string $targetType): void
    {
        // remove the truce status and setup target and accuracy
        $hunters = $this->daedalus->getAttackingHunters();
        /** @var Hunter $hunter */
        foreach ($hunters as $hunter) {
            $status = $hunter->getStatusByName(HunterStatusEnum::HUNTER_CHARGE);
            $hunter->removeStatus($status);

            $hunterTarget = new HunterTarget($hunter);
            switch ($targetType) {
                case HunterTargetEnum::DAEDALUS:
                    $hunterTarget->setTargetEntity($this->daedalus);
                    break;
                case HunterTargetEnum::PATROL_SHIP:
                    $hunterTarget->setTargetEntity($this->pasiphae);
                    break;
                default:
                    throw new \Exception('Unknown target type: ' . $targetType);
            }

            $I->haveInRepository($hunterTarget);

            $hunter->setTarget($hunterTarget);
            $hunter->getHunterConfig()->setHitChance(100);

            $I->haveInRepository($hunter);
        }

        $this->hunterService->makeHuntersShoot($hunters);
    }
}
