<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\InsertFuel;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class InsertFuelCest extends AbstractFunctionalTest
{
    private InsertFuel $insertFuelAction;
    private GameEquipmentServiceInterface $gameEquipmentService;

    private Action $actionConfig;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $storageRoom = $this->createExtraPlace(RoomEnum::REAR_ALPHA_STORAGE, $I, $this->daedalus);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->actionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::INSERT_FUEL]);

        $this->player->changePlace($storageRoom);

        $this->insertFuelAction = $I->grabService(InsertFuel::class);
    }

    public function testInsertFuel(FunctionalTester $I)
    {
        // Given the initial fuel
        $initFuel = $this->player->getDaedalus()->getFuel();

        // given there is a fuel tank in the room
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::FUEL_TANK,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given player has a fuel capsule in inventory
        $gameCapsule = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::FUEL_CAPSULE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // when player inserts fuel capsule in fuel tank
        $this->insertFuelAction->loadParameters($this->actionConfig, $this->player, $gameCapsule);
        $this->insertFuelAction->execute();

        // then Daedalus has 1 more fuel
        $I->assertEquals($initFuel + 1, $this->daedalus->getFuel());

        // then fuel capsule is removed from player's inventory
        $I->assertEmpty($this->player->getEquipments());

        // then a public log should specify that the player inserted the jar of alien oil in the fuel tank
        /** @var RoomLog $roomLog */
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => ActionLogEnum::INSERT_FUEL,
            ]
        );

        $I->assertEquals(
            expected: ItemEnum::FUEL_CAPSULE,
            actual: $roomLog->getParameters()['target_item']
        );
    }

    public function testInsertFuelBrokenTank(FunctionalTester $I)
    {
        // Given the initial fuel
        $initFuel = $this->player->getDaedalus()->getFuel();

        // given there is a fuel tank in the room
        $tank = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::FUEL_TANK,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given player has a fuel capsule in inventory
        $gameCapsule = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::FUEL_CAPSULE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);
        $statusService->createStatusFromName(EquipmentStatusEnum::BROKEN, $tank, [], new \DateTime());

        $this->insertFuelAction->loadParameters($this->actionConfig, $this->player, $gameCapsule);

        $I->assertFalse($this->insertFuelAction->isVisible());
    }

    public function testInsertJarOfAlienOilSuccess(FunctionalTester $I): void
    {
        // given Daedalus has 0 fuel
        $this->player->getDaedalus()->setFuel(0);

        // given there is a fuel tank in the room
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::FUEL_TANK,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given player has a jar of alien oil in inventory
        $jarOfAlienOil = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::JAR_OF_ALIEN_OIL,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // when player inserts jar of alien oil in fuel tank
        $this->insertFuelAction->loadParameters($this->actionConfig, $this->player, $jarOfAlienOil);
        $this->insertFuelAction->execute();

        // then jar of alien oil is removed from player's inventory
        $I->assertFalse($this->player->hasEquipmentByName(ToolItemEnum::JAR_OF_ALIEN_OIL));

        // then daedalus has 5 more fuel
        $I->assertEquals(5, $this->player->getDaedalus()->getFuel());

        // then a public log should specify that the player inserted the jar of alien oil in the fuel tank
        /** @var RoomLog $roomLog */
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => ActionLogEnum::INSERT_FUEL,
            ]
        );

        $I->assertEquals(
            expected: ToolItemEnum::JAR_OF_ALIEN_OIL,
            actual: $roomLog->getParameters()['target_item']
        );
    }
}
