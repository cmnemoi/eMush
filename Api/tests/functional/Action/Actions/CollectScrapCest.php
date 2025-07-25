<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\CollectScrap;
use Mush\Action\Actions\Land;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\SpaceShipConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class CollectScrapCest extends AbstractFunctionalTest
{
    private ActionConfig $collectScrapActionConfig;
    private ActionConfig $landActionConfig;
    private CollectScrap $collectScrapAction;
    private EventServiceInterface $eventService;
    private GameEquipment $pasiphae;
    private ChargeStatus $pasiphaeArmor;
    private GameEquipment $patrolShip;
    private ChargeStatus $patrolShipArmor;
    private Land $landAction;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private CreateHunterService $createHunter;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $this->player1->changePlace($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));

        $this->collectScrapActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::COLLECT_SCRAP]);
        $this->landActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::LAND]);

        $this->collectScrapAction = $I->grabService(CollectScrap::class);
        $this->landAction = $I->grabService(Land::class);

        /** @var SpaceShipConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(SpaceShipConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $this->pasiphae = new SpaceShip($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));
        $this->pasiphae
            ->setPatrolShipName(EquipmentEnum::PASIPHAE)
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
            ->setDockingPlace(RoomEnum::ALPHA_BAY_2);
        $I->haveInRepository($this->pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorConfig */
        $pasiphaeArmorConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $this->pasiphaeArmor = new ChargeStatus($this->pasiphae, $pasiphaeArmorConfig);

        /** @var SpaceShipConfig $patrolShipConfig */
        $patrolShipConfig = $I->grabEntityFromRepository(SpaceShipConfig::class, ['equipmentName' => EquipmentEnum::PATROL_SHIP]);
        $this->patrolShip = new SpaceShip($this->daedalus->getPlaceByName(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN));
        $this->patrolShip
            ->setName(EquipmentEnum::PATROL_SHIP)
            ->setEquipment($patrolShipConfig)
            ->setPatrolShipName(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN)
            ->setDockingPlace(RoomEnum::ALPHA_BAY_2);
        $I->haveInRepository($this->patrolShip);

        /** @var ChargeStatusConfig $patrolShipArmorConfig */
        $patrolShipArmorConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_default']);
        $this->patrolShipArmor = new ChargeStatus($this->patrolShip, $patrolShipArmorConfig);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->createHunter = $I->grabService(CreateHunterService::class);
    }

    public function testCollectScrapActionNoScrapToCollect(FunctionalTester $I): void
    {
        $this->collectScrapAction->loadParameters(
            actionConfig: $this->collectScrapActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );

        $I->assertFalse($this->collectScrapAction->isVisible());
    }

    public function testCollectScrapActionNotVisibleInPatrolShipIfPasiphaeIsStillLiving(FunctionalTester $I): void
    {
        // given there is some scrap in space
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        // given player is in a patrol ship
        $this->player1->changePlace($this->daedalus->getPlaceByName(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN));

        // when player tries to collect scrap
        $this->collectScrapAction->loadParameters(
            actionConfig: $this->collectScrapActionConfig,
            actionProvider: $this->patrolShip,
            player: $this->player1,
            target: $this->patrolShip
        );

        // then collect scrap action should not be visible
        $I->assertFalse($this->collectScrapAction->isVisible());
    }

    public function testCollectScrapActionVisibleInPatrolShipIfPasiphaeDestroyed(FunctionalTester $I): void
    {
        // given there is some scrap in space
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        // given player is in a patrol ship
        $this->player1->changePlace($this->daedalus->getPlaceByName(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN));

        // given Pasiphae is destroyed
        $interactEvent = new InteractWithEquipmentEvent(
            $this->pasiphae,
            null,
            VisibilityEnum::HIDDEN,
            ['test'],
            new \DateTime()
        );

        $this->eventService->callEvent($interactEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        // when player tries to collect scrap
        $this->collectScrapAction->loadParameters(
            actionConfig: $this->collectScrapActionConfig,
            actionProvider: $this->patrolShip,
            player: $this->player1,
            target: $this->patrolShip
        );

        // then collect scrap action should be visible
        $I->assertTrue($this->collectScrapAction->isVisible());
    }

    public function testCollectScrapActionSuccess(FunctionalTester $I): void
    {
        // spawn some scrap in space
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->player1->getDaedalus()->getSpace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        $this->collectScrapAction->loadParameters(
            actionConfig: $this->collectScrapActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $I->assertTrue($this->collectScrapAction->isVisible());
        $I->assertNull($this->collectScrapAction->cannotExecuteReason());

        $result = $this->collectScrapAction->execute();

        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->collectScrapActionConfig->getActionCost()
        );

        $I->assertInstanceOf(Success::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::SCRAP_COLLECTED,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertEquals(
            $this->player1->getDaedalus()->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getInitHull(),
            $this->player1->getDaedalus()->getHull()
        );
        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player1->getHealthPoint()
        );
        $I->assertEquals(
            $this->pasiphaeArmor->getThreshold(),
            $this->pasiphaeArmor->getCharge()
        );
    }

    public function testCollectScrapWithAttackingHunters(FunctionalTester $I): void
    {
        // spawn some scrap in space
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        // spawn some hunters
        $hunterEvent = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime(),
        );
        $this->eventService->callEvent($hunterEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $I->assertNotEmpty($this->daedalus->getHuntersAroundDaedalus());

        $this->collectScrapAction->loadParameters(
            actionConfig: $this->collectScrapActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $I->assertTrue($this->collectScrapAction->isVisible());
        $I->assertNull($this->collectScrapAction->cannotExecuteReason());

        $result = $this->collectScrapAction->execute();

        $I->assertEquals(
            expected: $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->collectScrapActionConfig->getActionCost(),
            actual: $this->player1->getActionPoint(),
        );
        $I->assertNotEquals(
            expected: $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            actual: $this->player1->getHealthPoint(),
        );

        /** @var ChargeStatus $status */
        $status = $this->pasiphae->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        $this->pasiphaeArmor = $status;
        $I->assertNotEquals(
            $this->pasiphaeArmor->getThreshold(),
            $this->pasiphaeArmor->getCharge()
        );

        $I->assertInstanceOf(Success::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::SCRAP_COLLECTED,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::SPACE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::SCRAP_COLLECTED,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertNotEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player1->getHealthPoint()
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::ATTACKED_BY_HUNTER,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::PATROL_DAMAGE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testCollectScrapWithAttackingAsteroids(FunctionalTester $I): void
    {
        // given there is some scrap in space
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        // given only asteroids can spawn
        $this->daedalus->getGameConfig()->setHunterConfigs(
            $this->daedalus->getGameConfig()->getHunterConfigs()->filter(
                static fn (HunterConfig $hunterConfig) => $hunterConfig->getHunterName() === HunterEnum::ASTEROID
            )
        );
        // given it's day 10 so asteroids can spawn
        $this->daedalus->setDay(10);

        // given some asteroids are spawn
        $hunterEvent = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime(),
        );
        $this->eventService->callEvent($hunterEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $I->assertNotEmpty($this->daedalus->getHuntersAroundDaedalus()->getAllHuntersByType(HunterEnum::ASTEROID));
        $I->assertEmpty($this->daedalus->getHuntersAroundDaedalus()->getAllExceptType(HunterEnum::ASTEROID));

        // when player collects scrap
        $this->collectScrapAction->loadParameters(
            actionConfig: $this->collectScrapActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->collectScrapAction->execute();

        // then player should not be damaged
        $I->assertEquals(
            expected: $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            actual: $this->player1->getHealthPoint(),
        );

        // then pasiphae should not be damaged
        /** @var ChargeStatus $status */
        $status = $this->pasiphae->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        $this->pasiphaeArmor = $status;
        $I->assertEquals(
            $this->pasiphaeArmor->getThreshold(),
            $this->pasiphaeArmor->getCharge()
        );
    }

    public function testLandSuccessWithScrapCollected(FunctionalTester $I): void
    {
        $this->testCollectScrapActionSuccess($I);

        $alphaBay2 = $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2);
        $I->assertFalse($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));

        $this->landActionConfig->setCriticalRate(100); // 100% critical rate so landing is always successful
        $this->landAction->loadParameters(
            actionConfig: $this->landActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        $I->assertTrue($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::PATROL_DISCHARGE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testLandSuccessWithScrapCollectedButPasiphaeIsDestroyed(FunctionalTester $I): void
    {
        // given we collected scrap successfully
        $this->testCollectScrapActionSuccess($I);

        // given land action has a 0% critical rate it will fail and pasiphae will be damaged
        $this->landActionConfig->setCriticalRate(0);

        // given pasiphae has 1 armor so it will be destroyed at landing
        $this->pasiphaeArmor->setCharge(1);

        // when player lands
        $this->landAction->loadParameters(
            actionConfig: $this->landActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        // then there should not be scrap in alpha bay 2
        $alphaBay2 = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::ALPHA_BAY_2);
        $I->assertFalse($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));
    }

    public function testLandSuccessWithoutScrapCollected($I): void
    {
        $alphaBay2 = $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2);
        $I->assertFalse($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));

        $this->landAction->loadParameters(
            actionConfig: $this->landActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        $I->assertFalse($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::PATROL_DISCHARGE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testLandFailWithScrapCollected(FunctionalTester $I): void
    {
        $this->testCollectScrapActionSuccess($I);

        $alphaBay2 = $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2);
        $I->assertFalse($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));

        $this->landActionConfig->setCriticalRate(0); // 0% critical rate so landing will fail
        $this->landAction->loadParameters(
            actionConfig: $this->landActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        $I->assertTrue($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::PATROL_DISCHARGE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function shouldPrintFailedLogWhenNoScrapIsCollected(FunctionalTester $I): void
    {
        // given there is some scrap in space
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        // given player is in a patrol ship
        $this->player1->changePlace($this->daedalus->getPlaceByName(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN));

        // given Pasiphae is destroyed
        $interactEvent = new InteractWithEquipmentEvent(
            $this->pasiphae,
            null,
            VisibilityEnum::HIDDEN,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($interactEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        // patrol ship cannot collect scrap
        $this->patrolShip->getEquipment()->setCollectScrapNumber([0 => 1]);

        $this->collectScrapAction->loadParameters(
            actionConfig: $this->collectScrapActionConfig,
            actionProvider: $this->patrolShip,
            player: $this->player1,
            target: $this->patrolShip
        );
        $this->collectScrapAction->execute();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Chun** tente un looping audacieux pour attraper un débris en plein vol, mais ne collecte rien...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player1,
                log: ActionLogEnum::COLLECT_SCRAP_FAIL,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }

    public function transportShouldNotHurtPlayer(FunctionalTester $I): void
    {
        // given there is a transport
        $this->createHunter->execute(HunterEnum::TRANSPORT, $this->daedalus->getId());

        // given there is some scrap in space
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        // when player collects scrap
        $this->collectScrapAction->loadParameters(
            actionConfig: $this->collectScrapActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->collectScrapAction->execute();

        // then player should not be damaged
        $I->assertEquals(
            expected: $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            actual: $this->player1->getHealthPoint(),
        );
    }

    private function createExtraRooms(FunctionalTester $I, Daedalus $daedalus): void
    {
        /** @var PlaceConfig $pasiphaeRoomConfig */
        $pasiphaeRoomConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::PASIPHAE]);
        $pasiphaeRoom = new Place();
        $pasiphaeRoom
            ->setName(RoomEnum::PASIPHAE)
            ->setType($pasiphaeRoomConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($pasiphaeRoom);

        $alphaBay2Config = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::ALPHA_BAY_2]);
        $alphaBay2 = new Place();
        $alphaBay2
            ->setName(RoomEnum::ALPHA_BAY_2)
            ->setType($alphaBay2Config->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($alphaBay2);

        $patrolShipRoomConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::PATROL_SHIP_ALPHA_TAMARIN]);
        $patrolShipRoom = new Place();
        $patrolShipRoom
            ->setName(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN)
            ->setType($patrolShipRoomConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($patrolShipRoom);

        $I->refreshEntities($daedalus);
    }
}
