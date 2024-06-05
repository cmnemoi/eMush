<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Takeoff;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TakeoffActionCest extends AbstractFunctionalTest
{
    private Takeoff $takeoffAction;
    private ActionConfig $action;

    private GameEquipment $pasiphae;
    private ChargeStatus $pasiphaeArmor;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $this->pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $this->pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($this->pasiphae);

        $this->pasiphaeArmor = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            $this->pasiphae,
            [],
            new \DateTime()
        );
        $I->haveInRepository($this->pasiphae);

        // given player1 is a pilot so they can take off
        $this->statusService->createStatusFromName(
            SkillEnum::PILOT,
            $this->player1,
            [],
            new \DateTime()
        );

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKEOFF]);
        $this->takeoffAction = $I->grabService(Takeoff::class);
    }

    public function shouldNotBeExecutableIfPlayerIsNotAPilot(FunctionalTester $I): void
    {
        // given KT is not a pilot (default)

        // when KT tries to take off
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->kuanTi,
            target: $this->pasiphae
        );

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::TERMINAL_NERON_LOCK, $this->takeoffAction->cannotExecuteReason());
    }

    public function testTakeoffCriticalSuccess(FunctionalTester $I)
    {
        $this->action->setCriticalRate(100);
        $I->haveInRepository($this->action);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        $pasiphaeArmor = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            $pasiphae,
            [],
            new \DateTime()
        );
        $I->haveInRepository($pasiphae);

        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae
        );
        $I->assertTrue($this->takeoffAction->isVisible());
        $I->assertNull($this->takeoffAction->cannotExecuteReason());

        $result = $this->takeoffAction->execute();

        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );

        $I->assertInstanceOf(CriticalSuccess::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::TAKEOFF_SUCCESS,
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
            $pasiphaeArmor->getThreshold(),
            $pasiphaeArmor->getCharge()
        );
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::TAKEOFF_NO_PILOT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertFalse($this->takeoffAction->isVisible());
    }

    public function testTakeoffSuccess(FunctionalTester $I): void
    {
        $this->action->setCriticalRate(0);
        $I->haveInRepository($this->action);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmor = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            $pasiphae,
            [],
            new \DateTime()
        );

        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae
        );
        $I->assertTrue($this->takeoffAction->isVisible());
        $I->assertNull($this->takeoffAction->cannotExecuteReason());

        $result = $this->takeoffAction->execute();

        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->assertNotEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player1->getHealthPoint()
        );
        $I->assertNotEquals(
            $pasiphaeArmor->getThreshold(),
            $pasiphaeArmor->getCharge()
        );

        $I->assertInstanceOf(Success::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::TAKEOFF_NO_PILOT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::PATROL_DAMAGE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertNotEquals(
            $this->player1->getDaedalus()->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getInitHull(),
            $this->player1->getDaedalus()->getHull()
        );
    }

    public function testTakeOffNotExecutableIfDaedalusIsTraveling(FunctionalTester $I): void
    {
        // given a pasiphae
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        $pasiphaeArmor = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            $pasiphae,
            [],
            new \DateTime()
        );
        $I->haveInRepository($pasiphae);

        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            DaedalusStatusEnum::TRAVELING,
            $this->daedalus,
            [],
            new \DateTime()
        );

        // when player tries to take off
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae
        );
        $this->takeoffAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::DAEDALUS_TRAVELING, $this->takeoffAction->cannotExecuteReason());
    }

    public function testTakeOffActionDropCriticalItems(FunctionalTester $I): void
    {
        // given a pasiphae
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        $pasiphaeArmor = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            $pasiphae,
            [],
            new \DateTime()
        );
        $I->haveInRepository($pasiphaeArmor);

        // given player has the extinguisher in their inventory
        $takeOffRoom = $this->player->getPlace();
        $extinguisherConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::EXTINGUISHER]);
        $extinguisher = new GameItem($this->player);
        $extinguisher
            ->setName(ToolItemEnum::EXTINGUISHER)
            ->setEquipment($extinguisherConfig);
        $I->haveInRepository($extinguisher);

        // when player tries to take off
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae
        );
        $this->takeoffAction->execute();

        // then the extinguisher is dropped in the take off room
        $I->assertFalse($this->player1->hasEquipmentByName(ToolItemEnum::EXTINGUISHER));
    }

    public function testTakeOffActionDropCriticalItemsIfPlayerIsMush(FunctionalTester $I): void
    {
        // given a pasiphae
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        $pasiphaeArmor = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            $pasiphae,
            [],
            new \DateTime()
        );
        $I->haveInRepository($pasiphaeArmor);

        // given player has the extinguisher and the hacker kit in their inventory
        $takeOffRoom = $this->player->getPlace();
        $extinguisherConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::EXTINGUISHER]);
        $extinguisher = new GameItem($this->player);
        $extinguisher
            ->setName(ToolItemEnum::EXTINGUISHER)
            ->setEquipment($extinguisherConfig);
        $I->haveInRepository($extinguisher);

        $hackerKitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::HACKER_KIT]);
        $hackerKit = new GameItem($this->player);
        $hackerKit
            ->setName(ToolItemEnum::HACKER_KIT)
            ->setEquipment($hackerKitConfig);
        $I->haveInRepository($hackerKit);

        // given player is Mush
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::MUSH,
            $this->player,
            [],
            new \DateTime()
        );

        // when player tries to take off
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae
        );
        $this->takeoffAction->execute();

        // then the hacker kit is dropped in the take off room but not the extinguisher
        $I->assertFalse($this->player1->hasEquipmentByName(ToolItemEnum::HACKER_KIT));
        $I->assertTrue($this->player1->hasEquipmentByName(ToolItemEnum::EXTINGUISHER));
    }

    public function shouldDestroyPatrolShipIfNoEnoughArmor(FunctionalTester $I): void
    {
        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at takeoffing
        $this->pasiphaeArmor->setCharge(1);

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then pasiphae is destroyed
        $I->dontSeeInRepository(GameEquipment::class, ['name' => EquipmentEnum::PASIPHAE]);
    }

    public function shouldKillPlayerWithoutSpacesuitIfPatrolShipExplodes(FunctionalTester $I): void
    {
        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at takeoffing
        $this->pasiphaeArmor->setCharge(1);

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then player is dead from patrol ship explosion
        $I->assertEquals(
            expected: EndCauseEnum::PATROL_SHIP_EXPLOSION,
            actual: $this->player->getPlayerInfo()->getClosedPlayer()->getEndCause(),
        );
    }

    public function shouldNotKillPlayerWithSpacesuitIfPatrolShipExplodes(FunctionalTester $I): void
    {
        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at takeoffing
        $this->pasiphaeArmor->setCharge(1);

        // given player has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then player is alive
        $I->assertTrue($this->player->isAlive());
    }

    public function shouldMovePatrolShipContentInSpaceIfPatrolShipExplodes(FunctionalTester $I): void
    {
        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at takeoffing
        $this->pasiphaeArmor->setCharge(1);

        // given there is an old shirt in pasiphae
        $oldShirt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OLD_T_SHIRT,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::PASIPHAE),
            reasons: [],
            time: new \DateTime()
        );

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then old shirt is in space
        $I->assertEquals(
            expected: RoomEnum::SPACE,
            actual: $oldShirt->getPlace()->getName(),
        );
    }

    public function shouldKillPlayerByInjuryIfTheyDontHaveHealthPoints(FunctionalTester $I): void
    {
        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given player has only one health point
        $this->player1->setHealthPoint(1);

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then player is dead from injury
        $I->assertEquals(
            expected: EndCauseEnum::INJURY,
            actual: $this->player1->getPlayerInfo()->getClosedPlayer()->getEndCause(),
        );
    }

    public function shouldHaveIncreasedChanceOfCriticalSuccessWithBayDoorXXLProject(FunctionalTester $I): void
    {
        // given takeoff action has a 75% critical rate
        $this->action->setCriticalRate(75);

        // given Bay Door XXL project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::BAY_DOOR_XXL),
            author: $this->player,
            I: $I
        );

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $result = $this->takeoffAction->execute();

        // then takeoff should always be a critical success thanks to increased chances
        $I->assertInstanceOf(CriticalSuccess::class, $result);
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

        $I->haveInRepository($daedalus);
    }
}
