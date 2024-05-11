<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\CheckFuelChamberLevel;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
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
final class CheckFuelChamberLevelCest extends AbstractFunctionalTest
{
    private ActionConfig $checkFuelChamberLevelActionConfig;
    private CheckFuelChamberLevel $checkFuelChamberLevelAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $engineRoom = $this->createExtraPlace(RoomEnum::ENGINE_ROOM, $I, $this->daedalus);

        $this->checkFuelChamberLevelActionConfig = $I->grabEntityFromRepository(ActionConfig::class, [
            'actionName' => ActionEnum::CHECK_FUEL_CHAMBER_LEVEL,
        ]);
        $this->checkFuelChamberLevelAction = $I->grabService(CheckFuelChamberLevel::class);

        $this->player = $this->player1;
        $this->player->changePlace($engineRoom);
    }

    public function testCheckFuelChamberLevelPrintsLog(FunctionalTester $I): void
    {
        $combustionChamber = $this->givenACombustionChamberInEngineRoom($I);

        $this->whenPlayerChecksFuelLevelOnCombustionChamber($combustionChamber);

        // then log is printed
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ENGINE_ROOM,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::CHECK_FUEL_CHAMBER_LEVEL_SUCCESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testCheckFuelChamberLevelNotExecutableIfCombustionChamberIsBroken(FunctionalTester $I): void
    {
        $combustionChamber = $this->givenACombustionChamberInEngineRoom($I);

        // given combustion chamber is broken
        $brokenStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $brokenStatus = new Status($combustionChamber, $brokenStatusConfig);
        $I->haveInRepository($brokenStatus);

        // when action is loaded
        $this->checkFuelChamberLevelAction->loadParameters(
            actionConfig: $this->checkFuelChamberLevelActionConfig,
            actionProvider: $combustionChamber,
            player: $this->player,
            target: $combustionChamber
        );

        // then action is not visible
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            actual: $this->checkFuelChamberLevelAction->cannotExecuteReason()
        );
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

    private function whenPlayerChecksFuelLevelOnCombustionChamber(GameEquipment $combustionChamber): void
    {
        $this->checkFuelChamberLevelAction->loadParameters(
            actionConfig: $this->checkFuelChamberLevelActionConfig,
            actionProvider: $combustionChamber,
            player: $this->player,
            target: $combustionChamber
        );
        $this->checkFuelChamberLevelAction->execute();
    }
}
