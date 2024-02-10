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
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class InsertFuelCest extends AbstractFunctionalTest
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

        $this->insertFuelAction->loadParameters($this->actionConfig, $this->player, $gameCapsule);

        $this->insertFuelAction->execute();

        $I->assertEquals($initFuel + 1, $this->daedalus->getFuel());
        $I->assertEmpty($this->player->getEquipments());
        $I->assertCount(1, $this->player->getPlace()->getEquipments());
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
    }
}
