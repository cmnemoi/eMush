<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Takeoff;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
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

final class TakeoffActionCest extends AbstractFunctionalTest
{
    private StatusServiceInterface $statusService;
    private Takeoff $takeoffAction;
    private Action $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::TAKEOFF]);

        $this->takeoffAction = $I->grabService(Takeoff::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testTakeoffSuccess(FunctionalTester $I)
    {
        $this->action->setCriticalRate(100);
        $I->haveInRepository($this->action);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        $pasiphaeArmor = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            $pasiphae,
            [],
            new \DateTime()
        );
        $I->haveInRepository($pasiphae);

        $this->takeoffAction->loadParameters($this->action, $this->player1, $pasiphae);
        $I->assertTrue($this->takeoffAction->isVisible());
        $I->assertNull($this->takeoffAction->cannotExecuteReason());

        $result = $this->takeoffAction->execute();

        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );

        $I->assertInstanceOf(Success::class, $result);
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

    public function testTakeoffFail(FunctionalTester $I): void
    {
        $this->action->setCriticalRate(0);
        $I->haveInRepository($this->action);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmor = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            $pasiphae,
            [],
            new \DateTime()
        );

        $this->takeoffAction->loadParameters($this->action, $this->player1, $pasiphae);
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

        $I->assertInstanceOf(Fail::class, $result);
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
            'visibility' => VisibilityEnum::PRIVATE,
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
            ->setEquipment($pasiphaeConfig)
        ;
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
        $this->takeoffAction->loadParameters($this->action, $this->player1, $pasiphae);
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
            ->setEquipment($pasiphaeConfig)
        ;
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
            ->setEquipment($extinguisherConfig)
        ;
        $I->haveInRepository($extinguisher);

        // when player tries to take off
        $this->takeoffAction->loadParameters($this->action, $this->player, $pasiphae);
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
            ->setEquipment($pasiphaeConfig)
        ;
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
            ->setEquipment($extinguisherConfig)
        ;
        $I->haveInRepository($extinguisher);

        $hackerKitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::HACKER_KIT]);
        $hackerKit = new GameItem($this->player);
        $hackerKit
            ->setName(ToolItemEnum::HACKER_KIT)
            ->setEquipment($hackerKitConfig)
        ;
        $I->haveInRepository($hackerKit);

        // given player is Mush
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::MUSH,
            $this->player,
            [],
            new \DateTime()
        );

        // when player tries to take off
        $this->takeoffAction->loadParameters($this->action, $this->player, $pasiphae);
        $this->takeoffAction->execute();

        // then the hacker kit is dropped in the take off room but not the extinguisher
        $I->assertFalse($this->player1->hasEquipmentByName(ToolItemEnum::HACKER_KIT));
        $I->assertTrue($this->player1->hasEquipmentByName(ToolItemEnum::EXTINGUISHER));
    }

    private function createExtraRooms(FunctionalTester $I, Daedalus $daedalus): void
    {
        /** @var PlaceConfig $pasiphaeRoomConfig */
        $pasiphaeRoomConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::PASIPHAE]);
        $pasiphaeRoom = new Place();
        $pasiphaeRoom
            ->setName(RoomEnum::PASIPHAE)
            ->setType($pasiphaeRoomConfig->getType())
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($pasiphaeRoom);

        $I->haveInRepository($daedalus);
    }
}
