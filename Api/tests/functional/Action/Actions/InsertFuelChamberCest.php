<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\InsertFuelChamber;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class InsertFuelChamberCest extends AbstractFunctionalTest
{
    private ActionConfig $insertFuelChamberActionConfig;
    private InsertFuelChamber $insertFuelChamberAction;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $engineRoom = $this->createExtraPlace(RoomEnum::ENGINE_ROOM, $I, $this->daedalus);

        $this->player->changePlace($engineRoom);
        $this->insertFuelChamberActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::INSERT_FUEL_CHAMBER]);
        $this->insertFuelChamberAction = $I->grabService(InsertFuelChamber::class);
    }

    public function testInsertFuelChamberRemoveFuelCapsuleFromPlayerInventory(FunctionalTester $I): void
    {
        $this->givenACombustionChamberInEngineRoom($I);
        $fuelCapsule = $this->givenAFuelCapsuleInPlayerInventory($I);

        $this->whenPlayerInsertsFuelCapsuleInCombustionChamber($fuelCapsule);

        // then fuel capsule is removed from player's inventory
        $I->assertFalse($this->player->hasEquipmentByName(ItemEnum::FUEL_CAPSULE));
    }

    public function testInsertFuelChamberIncreasesDaedalusCombustionChamberFuelVariable(FunctionalTester $I): void
    {
        $this->givenACombustionChamberInEngineRoom($I);
        $fuelCapsule = $this->givenAFuelCapsuleInPlayerInventory($I);

        $this->whenPlayerInsertsFuelCapsuleInCombustionChamber($fuelCapsule);

        // then Daedalus combustionChamberFuel is increased by 1
        $I->assertEquals(1, $this->daedalus->getCombustionChamberFuel());
    }

    public function testInsertFuelChamberPrintsLog(FunctionalTester $I): void
    {
        $this->givenACombustionChamberInEngineRoom($I);
        $fuelCapsule = $this->givenAFuelCapsuleInPlayerInventory($I);

        $this->whenPlayerInsertsFuelCapsuleInCombustionChamber($fuelCapsule);

        // then log is printed
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ENGINE_ROOM,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::INSERT_FUEL_CHAMBER_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testInsertFuelChamberNotVisibleIfCombustionChamberIsNotPresent(FunctionalTester $I): void
    {
        $this->givenAFuelCapsuleInPlayerInventory($I);

        // when action is loaded
        $fuelCapsule = $this->player->getEquipmentByName(ItemEnum::FUEL_CAPSULE);
        $this->insertFuelChamberAction->loadParameters($this->insertFuelChamberActionConfig, $this->player, $fuelCapsule);

        // then action is not visible
        $I->assertFalse($this->insertFuelChamberAction->isVisible());
    }

    public function testInsertFuelChamberNotVisibleIfCombustionChamberIsBroken(FunctionalTester $I): void
    {
        $this->givenAFuelCapsuleInPlayerInventory($I);
        $combusterChamber = $this->givenACombustionChamberInEngineRoom($I);

        // given combustion chamber is broken
        $brokenStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $brokenStatus = new Status($combusterChamber, $brokenStatusConfig);
        $I->haveInRepository($brokenStatus);

        // when action is loaded
        $fuelCapsule = $this->player->getEquipmentByName(ItemEnum::FUEL_CAPSULE);
        $this->insertFuelChamberAction->loadParameters($this->insertFuelChamberActionConfig, $this->player, $fuelCapsule);

        // then action is not visible
        $I->assertFalse($this->insertFuelChamberAction->isVisible());
    }

    public function testInsertFuelChamberNotExecutableIfMaxAmountInCombustionChamber(FunctionalTester $I): void
    {
        $this->givenAFuelCapsuleInPlayerInventory($I);
        $this->givenACombustionChamberInEngineRoom($I);

        // given combustion chamber is full
        $this->daedalus->setCombustionChamberFuel(10000);

        // when action is loaded
        $fuelCapsule = $this->player->getEquipmentByName(ItemEnum::FUEL_CAPSULE);
        $this->insertFuelChamberAction->loadParameters($this->insertFuelChamberActionConfig, $this->player, $fuelCapsule);

        // then action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::COMBUSTION_CHAMBER_FULL,
            actual: $this->insertFuelChamberAction->cannotExecuteReason()
        );
    }

    public function testInsertJarOfAlienOilSuccess(FunctionalTester $I): void
    {
        // given Daedalus has 0 fuel
        $this->player->getDaedalus()->setFuel(0);

        $this->givenACombustionChamberInEngineRoom($I);

        // given player has a jar of alien oil in inventory
        /** @var GameEquipmentServiceInterface $gameEquipmentService */
        $gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $jarOfAlienOil = $gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::JAR_OF_ALIEN_OIL,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // when player inserts jar of alien oil in fuel tank
        $this->insertFuelChamberAction->loadParameters($this->insertFuelChamberActionConfig, $this->player, $jarOfAlienOil);
        $I->assertTrue($this->insertFuelChamberAction->isVisible());

        $this->insertFuelChamberAction->execute();

        // then jar of alien oil is removed from player's inventory
        $I->assertFalse($this->player->hasEquipmentByName(ToolItemEnum::JAR_OF_ALIEN_OIL));

        // then Daedalus combustionChamberFuel is increased by 1
        $I->assertEquals(5, $this->daedalus->getCombustionChamberFuel());
    }

    private function givenACombustionChamberInEngineRoom(FunctionalTester $I): GameEquipment
    {
        $combustionChamberConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMBUSTION_CHAMBER]);
        $combusterChamber = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::ENGINE_ROOM));
        $combusterChamber
            ->setName(EquipmentEnum::COMBUSTION_CHAMBER)
            ->setEquipment($combustionChamberConfig);
        $I->haveInRepository($combusterChamber);

        return $combusterChamber;
    }

    private function givenAFuelCapsuleInPlayerInventory(FunctionalTester $I): GameItem
    {
        $fuelCapsuleConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ItemEnum::FUEL_CAPSULE]);
        $fuelCapsule = new GameItem($this->player);
        $fuelCapsule
            ->setName(ItemEnum::FUEL_CAPSULE)
            ->setEquipment($fuelCapsuleConfig);
        $I->haveInRepository($fuelCapsule);

        return $fuelCapsule;
    }

    private function whenPlayerInsertsFuelCapsuleInCombustionChamber(GameItem $fuelCapsule): void
    {
        $this->insertFuelChamberAction->loadParameters($this->insertFuelChamberActionConfig, $this->player, $fuelCapsule);
        $this->insertFuelChamberAction->execute();
    }
}
