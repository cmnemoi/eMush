<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\ChangeDaedalusOrientation;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Enum\DaedalusOrientationEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class ChangeDaedalusOrientationCest extends AbstractFunctionalTest
{
    private Action $changeDaedalusOrientationActionConfig;
    private ChangeDaedalusOrientation $changeDaedalusOrientationAction;
    private GameEquipment $commandTerminal;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);

        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $this->commandTerminal = new GameEquipment($bridge);
        $this->commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig)
        ;
        $I->haveInRepository($this->commandTerminal);

        $this->player->changePlace($bridge);

        $this->changeDaedalusOrientationActionConfig = $I->grabEntityFromRepository(Action::class, [
            'name' => ActionEnum::CHANGE_DAEDALUS_ORIENTATION,
        ]);
        $this->changeDaedalusOrientationAction = $I->grabService(ChangeDaedalusOrientation::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testChangeDaedalusOrientationNotExecutableIfCommandTerminalIsBroken(FunctionalTester $I): void
    {
        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: ['test'],
            time: new \DateTime(),
        );

        // and command terminal is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->commandTerminal,
            tags: ['test'],
            time: new \DateTime(),
        );

        // when action is loaded
        $this->changeDaedalusOrientationAction->loadParameters(
            $this->changeDaedalusOrientationActionConfig,
            $this->player,
            target: $this->commandTerminal,
            parameters: ['orientation' => DaedalusOrientationEnum::NORTH]
        );

        // then action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $this->changeDaedalusOrientationAction->cannotExecuteReason());
    }

    public function testChangeDaedalusOrientationNotExecutableIfPlayerIsNotFocused(FunctionalTester $I): void
    {
        // when action is loaded
        $this->changeDaedalusOrientationAction->loadParameters(
            $this->changeDaedalusOrientationActionConfig,
            $this->player,
            target: $this->commandTerminal,
            parameters: ['orientation' => DaedalusOrientationEnum::NORTH]
        );

        // then action is not visible
        $I->assertFalse($this->changeDaedalusOrientationAction->isVisible());
    }

    public function testChangeDaedalusOrientationNotExecutableIfPlayerTriesToOrientateInTheSameDirection(FunctionalTester $I): void
    {
        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: ['test'],
            time: new \DateTime(),
        );

        // given Daedalus orientation is North
        $this->daedalus->setOrientation(DaedalusOrientationEnum::NORTH);

        // when action is loaded
        $this->changeDaedalusOrientationAction->loadParameters(
            $this->changeDaedalusOrientationActionConfig,
            $this->player,
            target: $this->commandTerminal,
            parameters: ['orientation' => DaedalusOrientationEnum::NORTH]
        );

        // then action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::NEED_TO_CHANGE_ORIENTATION, $this->changeDaedalusOrientationAction->cannotExecuteReason());
    }

    public function testChangeDaedalusOrientationActuallyChangesOrientation(FunctionalTester $I): void
    {
        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: ['test'],
            time: new \DateTime(),
        );

        // when player changes daedalus orientation
        $this->changeDaedalusOrientationAction->loadParameters(
            $this->changeDaedalusOrientationActionConfig,
            $this->player,
            target: $this->commandTerminal,
            parameters: ['orientation' => DaedalusOrientationEnum::NORTH]
        );
        $this->changeDaedalusOrientationAction->execute();

        // then Daedalus orientation is now North
        $I->assertEquals(DaedalusOrientationEnum::NORTH, $this->daedalus->getOrientation());
    }

    public function testChangeDaedalusOrientationFromNorthToEastCostsBaseCost(FunctionalTester $I): void
    {
        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: ['test'],
            time: new \DateTime(),
        );

        // given Daedalus orientation is North
        $this->daedalus->setOrientation(DaedalusOrientationEnum::NORTH);

        // given action base cost
        $baseCost = $this->changeDaedalusOrientationActionConfig->getActionCost();

        // when player changes daedalus orientation
        $this->changeDaedalusOrientationAction->loadParameters(
            $this->changeDaedalusOrientationActionConfig,
            $this->player,
            target: $this->commandTerminal,
            parameters: ['orientation' => DaedalusOrientationEnum::EAST]
        );
        $this->changeDaedalusOrientationAction->execute();

        // then the action costs base cost
        $I->assertEquals(
            expected: $this->player->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $baseCost,
            actual: $this->player->getActionPoint(),
        );
    }

    public function testChangeDaedalusOrientationFromNorthToWestCostsBaseCost(FunctionalTester $I): void
    {
        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: ['test'],
            time: new \DateTime(),
        );

        // given Daedalus orientation is North
        $this->daedalus->setOrientation(DaedalusOrientationEnum::NORTH);

        // given action base cost
        $baseCost = $this->changeDaedalusOrientationActionConfig->getActionCost();

        // when player changes daedalus orientation
        $this->changeDaedalusOrientationAction->loadParameters(
            $this->changeDaedalusOrientationActionConfig,
            $this->player,
            target: $this->commandTerminal,
            parameters: ['orientation' => DaedalusOrientationEnum::WEST]
        );
        $this->changeDaedalusOrientationAction->execute();

        // then the action costs base cost
        $I->assertEquals(
            expected: $this->player->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $baseCost,
            actual: $this->player->getActionPoint(),
        );
    }

    public function testChangeDaedalusOrientationFromNorthToSouthCostsOneExtraAP(FunctionalTester $I): void
    {
        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: ['test'],
            time: new \DateTime(),
        );

        // given Daedalus orientation is North
        $this->daedalus->setOrientation(DaedalusOrientationEnum::NORTH);

        // given action base cost
        $baseCost = $this->changeDaedalusOrientationActionConfig->getActionCost();

        // when player changes daedalus orientation to South
        $this->changeDaedalusOrientationAction->loadParameters(
            $this->changeDaedalusOrientationActionConfig,
            $this->player,
            target: $this->commandTerminal,
            parameters: ['orientation' => DaedalusOrientationEnum::SOUTH]
        );
        $this->changeDaedalusOrientationAction->execute();

        // then the action costs 1 extra AP from base cost
        $I->assertEquals(
            expected: $this->player->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - ($baseCost + 1),
            actual: $this->player->getActionPoint(),
        );
    }
}
