<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use DateTime;
use Mush\Action\Actions\ChangeDaedalusOrientation;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Enum\DaedalusOrientationEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\RoomEnum;
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

    public function testChangeDaedalusOrientationActuallyChangesOrientation(FunctionalTester $I): void
    {
        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: ['test'],
            time: new DateTime(),
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
}
