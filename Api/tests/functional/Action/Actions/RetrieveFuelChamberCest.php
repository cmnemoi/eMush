<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\RetrieveFuelChamber;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
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
final class RetrieveFuelChamberCest extends AbstractFunctionalTest
{
    private ActionConfig $retrieveFuelChamberActionConfig;
    private RetrieveFuelChamber $retrieveFuelChamberAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $engineRoom = $this->createExtraPlace(RoomEnum::ENGINE_ROOM, $I, $this->daedalus);

        $this->retrieveFuelChamberActionConfig = $I->grabEntityFromRepository(ActionConfig::class, [
            'actionName' => ActionEnum::RETRIEVE_FUEL_CHAMBER,
        ]);
        $this->retrieveFuelChamberAction = $I->grabService(RetrieveFuelChamber::class);

        $this->daedalus->setCombustionChamberFuel(1);

        $this->player = $this->player1;
        $this->player->changePlace($engineRoom);
    }

    public function testRetrieveFuelChamberCreatesFuelCapsuleInPlayerInventory(FunctionalTester $I): void
    {
        $combustionChamber = $this->givenACombustionChamberInEngineRoom($I);

        $this->whenPlayerRetrievesFuelCapsuleFromCombustionChamber($combustionChamber);

        // then fuel capsule is created in player's inventory
        $I->assertTrue($this->player->hasEquipmentByName(ItemEnum::FUEL_CAPSULE));
    }

    public function testRetrieveFuelChamberDecreasesDaedalusCombustionChamberFuelVariable(FunctionalTester $I): void
    {
        $combustionChamber = $this->givenACombustionChamberInEngineRoom($I);

        $this->whenPlayerRetrievesFuelCapsuleFromCombustionChamber($combustionChamber);

        // then Daedalus combustionChamberFuel is decreased by 1
        $I->assertEquals(0, $this->daedalus->getCombustionChamberFuel());
    }

    public function testRetrieveFuelChamberPrintsLog(FunctionalTester $I): void
    {
        $combustionChamber = $this->givenACombustionChamberInEngineRoom($I);

        $this->whenPlayerRetrievesFuelCapsuleFromCombustionChamber($combustionChamber);

        // then log is printed
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ENGINE_ROOM,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::RETRIEVE_FUEL_CHAMBER_SUCCESS,
            'visibility' => VisibilityEnum::SECRET,
        ]);
    }

    public function testRetrieveFuelChamberNotExecutableIfCombustionChamberIsBroken(FunctionalTester $I): void
    {
        $combustionChamber = $this->givenACombustionChamberInEngineRoom($I);

        // given combustion chamber is broken
        $brokenStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $brokenStatus = new Status($combustionChamber, $brokenStatusConfig);
        $I->haveInRepository($brokenStatus);

        // when action is loaded
        $this->retrieveFuelChamberAction->loadParameters(
            actionConfig: $this->retrieveFuelChamberActionConfig,
            actionProvider: $combustionChamber,
            player: $this->player,
            target: $combustionChamber
        );

        // then action is not visible
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            actual: $this->retrieveFuelChamberAction->cannotExecuteReason()
        );
    }

    public function testRetrieveFuelChamberNotVisibleIfNothingInCombustionChamber(FunctionalTester $I): void
    {
        $combustionChamber = $this->givenACombustionChamberInEngineRoom($I);

        // given combustion chamber is empty
        $this->daedalus->setCombustionChamberFuel(0);

        // when action is loaded
        $this->retrieveFuelChamberAction->loadParameters(
            actionConfig: $this->retrieveFuelChamberActionConfig,
            actionProvider: $combustionChamber,
            player: $this->player,
            target: $combustionChamber
        );

        // then action is not visible
        $I->assertFalse($this->retrieveFuelChamberAction->isVisible());
    }

    private function givenACombustionChamberInEngineRoom(FunctionalTester $I): GameEquipment
    {
        $combustionChamberConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMBUSTION_CHAMBER]);
        $combustionChamber = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::ENGINE_ROOM));
        $combustionChamber
            ->setName(EquipmentEnum::COMBUSTION_CHAMBER)
            ->setEquipment($combustionChamberConfig);
        $I->haveInRepository($combustionChamber);

        return $combustionChamber;
    }

    private function whenPlayerRetrievesFuelCapsuleFromCombustionChamber(GameEquipment $combustionChamber): void
    {
        $this->retrieveFuelChamberAction->loadParameters(
            actionConfig: $this->retrieveFuelChamberActionConfig,
            actionProvider: $combustionChamber,
            player: $this->player,
            target: $combustionChamber
        );
        $this->retrieveFuelChamberAction->execute();
    }
}
