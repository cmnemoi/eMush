<?php

namespace Mush\Tests\functional\Hunter\Service;

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
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterService;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
final class HunterServiceMakeHuntersShootCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private HunterService $hunterService;
    private GameEquipment $pasiphae;
    private ChargeStatusConfig $pasiphaeArmorStatusConfig;
    private ChargeStatus $pasiphaeArmorStatus;
    private Hunter $hunter;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->hunterService = $I->grabService(HunterService::class);

        $pasiphaeRoom = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $this->pasiphae = new GameEquipment($pasiphaeRoom);
        $this->pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($this->pasiphae);

        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);

        $this->pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);

        /** @var ChargeStatus $pasiphaeArmorStatus */
        $pasiphaeArmorStatus = $statusService->createStatusFromConfig(
            $this->pasiphaeArmorStatusConfig,
            $this->pasiphae,
            [],
            new \DateTime()
        );
        $this->pasiphaeArmorStatus = $pasiphaeArmorStatus;

        $this->player2->setPlace($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));
        $I->haveInRepository($this->player2);

        $this->daedalus->setHunterPoints(10); // spawn a single hunter
        $this->hunterService->unpoolHunters($this->daedalus, [], new \DateTime());

        $this->hunter = $this->daedalus->getAttackingHunters()->first();
        $this->hunter->setHitChance(100);

        $target = new HunterTarget($this->hunter);
        $target->setTargetEntity($this->daedalus);
        $this->hunter->setTarget($target);

        $I->haveInRepository($this->hunter);
    }

    public function testUnpoolHunters(FunctionalTester $I)
    {
        $this->hunterService->unpoolHunters($this->daedalus, [], new \DateTime());
        $I->assertCount(1, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testUnpoolHuntersSpawnMaxAsteroidPerWave(FunctionalTester $I)
    {
        // given only asteroids can spawn
        $this->daedalus->getGameConfig()->setHunterConfigs(
            $this->daedalus->getGameConfig()->getHunterConfigs()->filter(static fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::ASTEROID)
        );

        // given daedalus is D5 so asteroid can spawn
        $this->daedalus->setDay(5);

        // given daedalus has a lot of points
        $this->daedalus->setHunterPoints(1000);

        // when unpooling hunters
        $this->hunterService->unpoolHunters($this->daedalus, [], new \DateTime());

        // then only `maxAsteroidsPerWave` asteroids are spawned
        $maxAsteroidsPerWave = $this->daedalus->getGameConfig()->getHunterConfigs()->filter(static fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::ASTEROID)->first()->getMaxPerWave();
        $I->assertCount($maxAsteroidsPerWave, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::ASTEROID));
    }

    public function testMakeHuntersShootDaedalus(FunctionalTester $I)
    {
        // given hunter has a 0% chance to target any other target (default, so do nothing)

        // when hunter shoots
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then daedalus hull is damaged
        $I->assertLessThan(
            expected: $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull(),
            actual: $this->daedalus->getHull()
        );
    }

    public function testMakeHuntersShootPatrolShip(FunctionalTester $I)
    {
        // given hunter targets a patrol ship
        $target = new HunterTarget($this->hunter);
        $target->setTargetEntity($this->pasiphae);
        $this->hunter->setTarget($target);
        $I->haveInRepository($this->hunter);

        // when hunter shoots
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then patrol ship armor is damaged
        $I->assertLessThan(
            expected: $this->pasiphaeArmorStatusConfig->getStartCharge(),
            actual: $this->pasiphaeArmorStatus->getCharge()
        );

        // then I see a public room log stating the patrol ship armor is damaged
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->pasiphae->getPlace()->getLogName(),
                'log' => LogEnum::PATROL_DAMAGE,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testMakeHuntersShootPlayer(FunctionalTester $I)
    {
        // given hunter targets a player
        $target = new HunterTarget($this->hunter);
        $target->setTargetEntity($this->player2);
        $this->hunter->setTarget($target);
        $I->haveInRepository($this->hunter);

        // when hunter shoots
        $hunters = $this->daedalus->getAttackingHunters();
        $this->hunterService->makeHuntersShoot($hunters);

        $I->assertTrue($this->player2->isInAPatrolShip());

        // then player health is reduced
        $I->assertLessThan(
            expected: $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            actual: $this->player2->getHealthPoint(),
        );

        // then I see a private health loss room log
        $I->seeInRepository(
            entity: RoomLog::class,
            params : [
                'place' => $this->player2->getPlace()->getLogName(),
                'playerInfo' => $this->player2->getPlayerInfo(),
                'log' => PlayerModifierLogEnum::LOSS_HEALTH_POINT,
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );

        // then I see a public log explaining that player has been attacked by a hunter
        $I->seeInRepository(
            entity: RoomLog::class,
            params : [
                'place' => $this->player2->getPlace()->getLogName(),
                'log' => LogEnum::ATTACKED_BY_HUNTER,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testMakeHuntersDoNotShootEntitiesNotInBattle(FunctionalTester $I): void
    {
        // given hunter has a 100% chance to target a player, but the player is in laboratory (not in battle)
        $this->hunter->getHunterConfig()->addTargetProbability(target: HunterTargetEnum::PLAYER, probability: 100);
        $this->player2->setPlace($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $I->haveInRepository($this->player2);

        // when hunter shoots
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then player health is not reduced
        $I->assertEquals(
            expected: $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            actual: $this->player2->getHealthPoint(),
        );
    }

    public function testMakeHunterShootDestroyPatrolShipIfNoArmor(FunctionalTester $I): void
    {
        // given hunter targets a patrol ship and patrol ship armor status charge is 1
        $target = new HunterTarget($this->hunter);
        $target->setTargetEntity($this->pasiphae);
        $this->hunter->setTarget($target);
        $I->haveInRepository($this->hunter);

        $this->pasiphaeArmorStatus->setCharge(1);

        // when hunter shoots
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then patrol ship is destroyed and player is dead
        $I->dontSeeInRepository(GameEquipment::class, [
            'name' => EquipmentEnum::PASIPHAE,
        ]);
        $I->assertFalse($this->player2->isAlive());
    }

    public function testMakeHunterShootKillsPlayerIfNoHealth(FunctionalTester $I): void
    {
        // given hunter targets a player and player health is 1
        $target = new HunterTarget($this->hunter);
        $target->setTargetEntity($this->player2);
        $this->hunter->setTarget($target);
        $I->haveInRepository($this->hunter);

        $this->player2->setHealthPoint(1);

        // when hunter shoots
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then player is dead
        $I->assertFalse($this->player2->isAlive());
    }

    public function testMakeHuntersShootHitChanceAugmentsAfterFailedShot(FunctionalTester $I): void
    {
        // given hunter has a 0% chance to hit
        $this->hunter->setHitChance(0);
        $I->haveInRepository($this->hunter);

        // when hunter shoots
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then hunter hit chance is augmented by `bonusAfterFailedShot`
        $I->assertEquals(
            expected: 0 + $this->hunter->getHunterConfig()->getBonusAfterFailedShot(),
            actual: $this->hunter->getHitChance(),
        );
    }

    public function testMakeHuntersShootHitChanceGetsResetToDefaultAfterSuccessfulShot(FunctionalTester $I): void
    {
        // given hunter has a 100% chance to hit
        $this->hunter->setHitChance(100);
        $I->haveInRepository($this->hunter);

        // when hunter shoots
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then hunter hit chance is back to default
        $I->assertEquals(
            expected: $this->hunter->getHunterConfig()->getHitChance(),
            actual: $this->hunter->getHitChance(),
        );
    }

    public function testMakeHuntersShootAsteroidFullHealth(FunctionalTester $I): void
    {
        $daedalus = $this->getDaedalusForAsteroidTest($I);

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
            ->getAttackingHunters()
            ->filter(static fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
            ->first();
        $truceStatus = $asteroid->getStatusByName(HunterStatusEnum::ASTEROID_TRUCE_CYCLES);
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
        $daedalus = $this->getDaedalusForAsteroidTest($I);

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
            ->getAttackingHunters()
            ->filter(static fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
            ->first();
        $truceStatus = $asteroid->getStatusByName(HunterStatusEnum::ASTEROID_TRUCE_CYCLES);
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

    public function testMakeHuntersShootD1000ActsThreeTimesACycle(FunctionalTester $I): void
    {
        // given D1000 is spawned
        $daedalus = $this->createDaedalusForD1000Test($I);

        // when hunter shoots
        $this->hunterService->makeHuntersShoot($daedalus->getAttackingHunters());

        // then daedalus hull is damaged twice over the three actions it has
        // first time, d100 has a target so it shots
        // second time, d1000 has no target so it does not shoot
        // third time, d1000 has a target so it shots
        $I->assertEquals(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull() - 6 * 2,
            actual: $daedalus->getHull(),
        );
    }

    public function testMakeHuntersShootAsteroidNotDestroyedIfCantShoot(FunctionalTester $I)
    {
        $daedalus = $this->getDaedalusForAsteroidTest($I);

        /** @var Hunter $asteroid */
        $asteroid = $daedalus
            ->getAttackingHunters()
            ->filter(static fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
            ->first();
        $I->assertNotNull($asteroid->getStatusByName(HunterStatusEnum::ASTEROID_TRUCE_CYCLES));

        $this->hunterService->makeHuntersShoot($daedalus->getAttackingHunters());

        $I->assertEquals(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull(),
            actual: $daedalus->getHull()
        ); // asteroid should not deal damage
        $I->assertCount(1, $daedalus->getAttackingHunters()); // asteroid should not be destroyed
    }

    public function shouldNotReduceHullIfPlasmaShieldIfFinished(FunctionalTester $I): void
    {
        // given I have one hunter
        $this->createHunterTargetingDaedalusByName($I, HunterEnum::HUNTER);

        // given plasma shield project is finished
        $plasmaShield = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $this->finishProject($plasmaShield, $this->chun, $I);

        // when I make the hunter shoot
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then the hull should not be damaged
        $I->assertEquals(
            expected: $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull(),
            actual: $this->daedalus->getHull(),
        );
    }

    public function shouldReduceShieldIfPlasmaShieldIfFinished(FunctionalTester $I): void
    {
        // given I have one hunter
        $this->createHunterTargetingDaedalusByName($I, HunterEnum::HUNTER);

        // given plasma shield project is finished
        $plasmaShield = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $this->finishProject($plasmaShield, $this->chun, $I);
        $initShield = $this->daedalus->getShield();

        // when I make the hunter shoot
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then the shield should be damaged
        $I->assertLessThan(
            expected: $initShield,
            actual: $this->daedalus->getShield(),
        );
    }

    public function shouldReduceHullAndShieldIfShieldIsInsufficient(FunctionalTester $I): void
    {
        // given I have one hunter
        $this->createHunterTargetingDaedalusByName($I, HunterEnum::HUNTER);

        // given plasma shield project is finished
        $plasmaShield = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $this->finishProject($plasmaShield, $this->chun, $I);
        $this->daedalus->setShield(1);

        // when I make the hunter shoot
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then the hull should be damaged
        $I->assertLessThan(
            expected: $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull(),
            actual: $this->daedalus->getHull(),
        );

        // then the shield should be damaged
        $I->assertEquals(
            expected: 0,
            actual: $this->daedalus->getShield(),
        );
    }

    public function shouldNotReduceShieldIfHunterShootingIsAsteroid(FunctionalTester $I): void
    {
        // given I have one asteroid
        $daedalus = $this->getDaedalusForAsteroidTest($I);

        // remove asteroid truce status
        $asteroid = $daedalus->getAttackingHunters()->first();
        $truceStatus = $asteroid->getStatusByName(HunterStatusEnum::ASTEROID_TRUCE_CYCLES);
        $asteroid->removeStatus($truceStatus);

        // given plasma shield project is finished
        $plasmaShield = $daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $plasmaShield->makeProgress(100);
        $daedalus->setShield(5);

        // when I make the asteroid shoot
        $this->hunterService->makeHuntersShoot($daedalus->getAttackingHunters());

        // then the hull should be damaged
        $I->assertLessThan(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull(),
            actual: $daedalus->getHull(),
        );

        // then the shield should not be damaged
        $I->assertEquals(
            expected: 5,
            actual: $daedalus->getShield(),
        );
    }

    public function hunterShouldMakeOneLessDamageToHullWithArmourCorridorProject(FunctionalTester $I): void
    {
        // given I have one hunter
        $hunter = $this->createHunterTargetingDaedalusByName($I, HunterEnum::HUNTER);

        // given this hunter deals 6 damage
        $hunter->getHunterConfig()->setDamageRange([6 => 1]);

        // given Armour Corridor project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::ARMOUR_CORRIDOR),
            author: $this->player,
            I: $I
        );

        // when I make the hunter shoot
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then the hull should be damaged by 1 less
        $I->assertEquals(
            expected: $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull() - 5,
            actual: $this->daedalus->getHull(),
        );
    }

    public function asteroidShouldMakeOneLessDamageToHullWithArmourCorridorProject(FunctionalTester $I): void
    {
        // given I have one asteroid
        $daedalus = $this->getDaedalusForAsteroidTest($I);

        /** @var Hunter $asteroid */
        $asteroid = $daedalus->getAttackingHunters()->first();

        // remove asteroid truce status
        $truceStatus = $asteroid->getStatusByNameOrThrow(HunterStatusEnum::ASTEROID_TRUCE_CYCLES);
        $asteroid->removeStatus($truceStatus);

        // given this asteroid has 6 health
        $asteroid->setHealth(6);

        // given Armour Corridor project is finished
        $this->finishProject(
            project: $daedalus->getProjectByName(ProjectName::ARMOUR_CORRIDOR),
            author: $this->player,
            I: $I
        );

        // when I make the asteroid shoot
        $this->hunterService->makeHuntersShoot($daedalus->getAttackingHunters());

        // then the hull should be damaged by 1 less
        $I->assertEquals(
            expected: $daedalus->getGameConfig()->getDaedalusConfig()->getInitHull() - 5,
            actual: $daedalus->getHull(),
        );
    }

    public function shouldNotRaiseArmorWithArmoredCorridorsAndPlasmaShield(FunctionalTester $I): void
    {
        // given I have one hunter
        $hunter = $this->createHunterTargetingDaedalusByName($I, HunterEnum::HUNTER);

        // given this hunter deals 6 damage
        $hunter->getHunterConfig()->setDamageRange([6 => 1]);

        // given Armour Corridor project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::ARMOUR_CORRIDOR),
            author: $this->player,
            I: $I
        );

        // given plasma shield project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            author: $this->player,
            I: $I
        );

        // given hull is at 90
        $this->daedalus->setHull(90);

        // given shield is at 10
        $this->daedalus->setShield(10);

        // when I make the hunter shoot
        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());

        // then plasma shield should be damaged
        $I->assertEquals(
            expected: $this->daedalus->getShield(),
            actual: 4,
        );

        // then the hull should stay the same
        $I->assertEquals(
            expected: 90,
            actual: $this->daedalus->getHull(),
        );
    }

    private function getDaedalusForAsteroidTest(FunctionalTester $I): Daedalus
    {
        $this->daedalus
            ->setDay(5) // so asteroid can spawn
            ->setCycle(0);

        // only asteroids can spawn
        $gameConfig = $this->daedalus->getGameConfig();
        $gameConfig->setHunterConfigs(
            $gameConfig->getHunterConfigs()->filter(static fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::ASTEROID)
        );

        $I->haveInRepository($this->daedalus);

        $this->daedalus->setHunterPoints(25);
        $hunterPoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        /** @var Hunter $asteroid */
        $asteroid = $this->daedalus
            ->getAttackingHunters()
            ->filter(static fn ($hunter) => $hunter->getName() === HunterEnum::ASTEROID)
            ->first();
        $hunterTarget = new HunterTarget($asteroid);
        $hunterTarget->setTargetEntity($this->daedalus);
        $asteroid->setTarget($hunterTarget);
        $I->haveInRepository($asteroid);

        // delete hunters
        $hunters = $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER);
        foreach ($hunters as $hunter) {
            $this->daedalus->removeHunter($hunter);
        }

        return $this->daedalus;
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
            ->setCycleStartedAt(new \DateTime());

        $I->haveInRepository($daedalus);

        $projectConfig = $I->grabEntityFromRepository(ProjectConfig::class, ['name' => ProjectName::PLASMA_SHIELD]);
        $project = new Project($projectConfig, $daedalus);
        $I->haveInRepository($project);

        $this->daedalus->addProject($project);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        // only D1000 can spawn
        $gameConfig->setHunterConfigs(
            $gameConfig->getHunterConfigs()->filter(static fn ($hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::DICE)
        );

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $neron = new Neron();
        $I->haveInRepository($neron);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setName(Uuid::v4()->toRfc4122())
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        $I->haveInRepository($daedalusInfo);

        $places = $this->createPlaces($I, $daedalus);
        $daedalus->setPlaces($places);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $daedalus->setHunterPoints(30);
        $this->hunterService->unpoolHunters($daedalus, [], new \DateTime());

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
        $I->haveInRepository($d1000);

        return $daedalus;
    }

    private function createHunterTargetingDaedalusByName(FunctionalTester $I, string $hunterName): Hunter
    {
        /** @var HunterConfig $hunterConfig */
        $hunterConfig = $this->daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterName);

        $hunter = new Hunter($hunterConfig, $this->daedalus);
        $hunter->setHunterVariables($hunterConfig);
        $hunter->setHitChance(100);
        $hunter->getHunterConfig()->setDamageRange([6 => 1]);

        $this->daedalus->addHunter($hunter);

        $I->haveInRepository($hunter);
        $I->haveInRepository($this->daedalus);

        return $hunter;
    }
}
