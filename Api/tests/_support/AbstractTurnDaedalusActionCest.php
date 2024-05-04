<?php

declare(strict_types=1);

namespace Mush\Tests;

use Mush\Action\Actions\AbstractTurnDaedalusAction;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

abstract class AbstractTurnDaedalusActionCest extends AbstractFunctionalTest
{
    protected ActionConfig $turnDaedalusConfig;
    protected AbstractTurnDaedalusAction $turnDaedalusAction;
    protected GameEquipment $commandTerminal;
    protected GameEquipment $alphaLateralReactor;
    protected GameEquipment $bravoLateralReactor;
    protected StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->turnDaedalusConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TURN_DAEDALUS_LEFT]);
        $engineRoom = $this->createExtraPlace(RoomEnum::ENGINE_ROOM, $I, $this->daedalus);

        // given there is a command terminal in player's room
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $this->commandTerminal = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $this->commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig);
        $I->haveInRepository($this->commandTerminal);

        // given there is bravo lateral reactor in engine room
        $leftLateralReactorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::REACTOR_LATERAL_BRAVO]);
        $this->bravoLateralReactor = new GameEquipment($engineRoom);
        $this->bravoLateralReactor
            ->setName(EquipmentEnum::REACTOR_LATERAL_BRAVO)
            ->setEquipment($leftLateralReactorConfig);
        $I->haveInRepository($this->bravoLateralReactor);

        // given there is alpha lateral reactor in engine room
        $rightLateralReactorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::REACTOR_LATERAL_ALPHA]);
        $this->alphaLateralReactor = new GameEquipment($engineRoom);
        $this->alphaLateralReactor
            ->setName(EquipmentEnum::REACTOR_LATERAL_ALPHA)
            ->setEquipment($rightLateralReactorConfig);
        $I->haveInRepository($this->alphaLateralReactor);

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

    public function testTurnDaedalusActionNotVisibleIfPlayerNotFocusedOnCommandTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the astro terminal
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player turns daedalus
        $this->turnDaedalusAction->loadParameters($this->turnDaedalusConfig, $this->player, $this->commandTerminal);
        $this->turnDaedalusAction->execute();

        // then the action is not visible
        $I->assertFalse($this->turnDaedalusAction->isVisible());
    }

    public function testTurnDaedalusActionNotExecutableIfDaedalusIsTraveling(FunctionalTester $I): void
    {
        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player turns daedalus
        $this->turnDaedalusAction->loadParameters(
            actionConfig: $this->turnDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::DAEDALUS_TRAVELING, $this->turnDaedalusAction->cannotExecuteReason());
    }

    public function testTurnDaedalusActionNotExecutableIfDaedalusIsInOrbit(FunctionalTester $I): void
    {
        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player turns daedalus
        $this->turnDaedalusAction->loadParameters(
            actionConfig: $this->turnDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::DAEDALUS_IN_ORBIT, $this->turnDaedalusAction->cannotExecuteReason());
    }

    public function testTurnDaedalusActionNotExecutableIfCommandTerminalIsBroken(FunctionalTester $I): void
    {
        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->commandTerminal,
            tags: [],
            time: new \DateTime(),
        );

        // when player turns daedalus
        $this->turnDaedalusAction->loadParameters(
            actionConfig: $this->turnDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $this->turnDaedalusAction->cannotExecuteReason());
    }

    public function testTurnDaedalusActionSuccessTriggersANeronAnnouncement(FunctionalTester $I): void
    {
        // when player turns daedalus
        $this->turnDaedalusAction->loadParameters(
            actionConfig: $this->turnDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusAction->execute();

        // then a neron announcement is triggered
        $I->seeInRepository(
            Message::class,
            [
                'neron' => $this->daedalus->getDaedalusInfo()->getNeron(),
                'message' => NeronMessageEnum::CHANGE_HEADING,
            ]
        );
    }

    abstract public function testTurnDaedalusActionNotExecutableIfLateralReactorIsBroken(FunctionalTester $I): void;

    abstract public function testTurnDaedalusActionSuccessChangesCorrectlyDaedalusOrientation(FunctionalTester $I): void;
}
