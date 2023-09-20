<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\InsertFuelChamber;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class InsertFuelChamberCest extends AbstractFunctionalTest
{
    private Action $insertFuelChamberActionConfig;
    private InsertFuelChamber $insertFuelChamberAction;
    private Player $player;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $engineRoom = $this->createExtraPlace(RoomEnum::ENGINE_ROOM, $I, $this->daedalus);

        $this->player = $this->player1;
        $this->player->changePlace($engineRoom);
        $this->insertFuelChamberActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::INSERT_FUEL_CHAMBER]);
        $this->insertFuelChamberAction = $I->grabService(InsertFuelChamber::class);
    }

    public function testInsertFuelChamberRemoveFuelCapsuleFromPlayerInventory(FunctionalTester $I): void
    {
        $this->givenACombustionChamberInEngineRoom($I);
        $this->givenAFuelCapsuleInPlayerInventory($I);

        $this->whenPlayerInsertsFuelCapsuleInCombustionChamber();

        // then fuel capsule is removed from player's inventory
        $I->assertFalse($this->player->hasEquipmentByName(ItemEnum::FUEL_CAPSULE));
    }

    public function testInsertFuelChamberIncreasesDaedalusCombustionChamberFuelVariable(FunctionalTester $I): void
    {
        $this->givenACombustionChamberInEngineRoom($I);
        $this->givenAFuelCapsuleInPlayerInventory($I);

        $this->whenPlayerInsertsFuelCapsuleInCombustionChamber();

        // then Daedalus combustionChamberFuel is increased by 1
        $I->assertEquals(1, $this->daedalus->getCombustionChamberFuel());
    }

    private function givenACombustionChamberInEngineRoom(FunctionalTester $I): void
    {
        $combustionChamberConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMBUSTION_CHAMBER]);
        $combusterChamber = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::ENGINE_ROOM));
        $combusterChamber
            ->setName(EquipmentEnum::COMBUSTION_CHAMBER)
            ->setEquipment($combustionChamberConfig)
        ;
        $I->haveInRepository($combusterChamber);
    }

    private function givenAFuelCapsuleInPlayerInventory(FunctionalTester $I): void
    {
        $fuelCapsuleConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ItemEnum::FUEL_CAPSULE]);
        $fuelCapsule = new GameItem($this->player);
        $fuelCapsule
            ->setName(ItemEnum::FUEL_CAPSULE)
            ->setEquipment($fuelCapsuleConfig)
        ;
        $I->haveInRepository($fuelCapsule);
    }

    private function whenPlayerInsertsFuelCapsuleInCombustionChamber(): void
    {
        $fuelCapsule = $this->player->getEquipmentByName(ItemEnum::FUEL_CAPSULE);
        $this->insertFuelChamberAction->loadParameters($this->insertFuelChamberActionConfig, $this->player, $fuelCapsule);
        $this->insertFuelChamberAction->execute();
    }
}
