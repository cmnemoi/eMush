<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\TurnDaedalusLeft;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class TurnDaedalusLeftCest extends AbstractFunctionalTest
{
    private Action $turnDaedalusLeftConfig;
    private TurnDaedalusLeft $turnDaedalusLeftAction;
    private GameEquipment $commandTerminal;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->turnDaedalusLeftConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::TURN_DAEDALUS_LEFT]);
        $this->turnDaedalusLeftAction = $I->grabService(TurnDaedalusLeft::class);
        
        // given there is a command terminal in player's room
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $this->commandTerminal = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $this->commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig)
        ;
        $I->haveInRepository($this->commandTerminal);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given player is focused on the astro terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->commandTerminal,
        );
    }

    public function testTurnDaedalusLeftNotVisibleIfPlayerNotFocusedOnCommandTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the astro terminal
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player turns daedalus left
        $this->turnDaedalusLeftAction->loadParameters($this->turnDaedalusLeftConfig, $this->player, $this->commandTerminal);
        $this->turnDaedalusLeftAction->execute();

        // then the action is not visible
        $I->assertFalse($this->turnDaedalusLeftAction->isVisible());
    }

    public function testTurnDaedalusLeftNotExecutableIfDaedalusIsTraveling(FunctionalTester $I): void
    {
        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player turns daedalus left
        $this->turnDaedalusLeftAction->loadParameters(
            action: $this->turnDaedalusLeftConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusLeftAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::DAEDALUS_TRAVELING, $this->turnDaedalusLeftAction->cannotExecuteReason());
    }

    public function testTurnDaedalusLeftNotExecutableIfCommandTerminalIsBroken(FunctionalTester $I): void
    {
        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->commandTerminal,
            tags: [],
            time: new \DateTime(),
        );

        // when player turns daedalus left
        $this->turnDaedalusLeftAction->loadParameters(
            action: $this->turnDaedalusLeftConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusLeftAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $this->turnDaedalusLeftAction->cannotExecuteReason());
    }

}