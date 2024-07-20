<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ToggleMagneticNet;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ToggleMagneticNetCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ToggleMagneticNet $toggleMagneticNet;
    private GameEquipment $biosTerminal;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TOGGLE_MAGNETIC_NET]);
        $this->toggleMagneticNet = $I->grabService(ToggleMagneticNet::class);

        $this->givenABiosTerminalInChunRoom($I->grabService(GameEquipmentServiceInterface::class));
        $this->givenChunIsFocusedOnBiosTerminal($I->grabService(StatusServiceInterface::class));
    }

    public function shouldNotBeVisibleIfMagneticNetIsNotFinished(FunctionalTester $I): void
    {
        $this->givenMagneticNetIsActive();

        $this->whenPlayerTogglesMagneticNet();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldActivateInactiveNet(FunctionalTester $I): void
    {
        $this->givenMagneticNetIsFinished($I);
        $this->givenMagneticNetIsInactive();

        $this->whenPlayerTogglesMagneticNet();

        $this->thenMagneticNetIsActive($I);
    }

    public function shouldDeactivateActiveNet(FunctionalTester $I): void
    {
        $this->givenMagneticNetIsFinished($I);
        $this->givenMagneticNetIsActive();

        $this->whenPlayerTogglesMagneticNet();

        $this->thenMagneticNetIsInactive($I);
    }

    public function shouldPrintAPrivateLog(FunctionalTester $I): void
    {
        $this->givenMagneticNetIsFinished($I);
        $this->givenMagneticNetIsInactive();

        $this->whenPlayerTogglesMagneticNet();

        $this->thenPrivateLogIsPrinted($I);
    }

    private function givenABiosTerminalInChunRoom(GameEquipmentServiceInterface $gameEquipmentService): void
    {
        // given I have a Bios terminal in Chun's room
        $this->biosTerminal = $gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::BIOS_TERMINAL,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsFocusedOnBiosTerminal(StatusServiceInterface $statusService): void
    {
        $statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );
    }

    private function givenMagneticNetIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::MAGNETIC_NET),
            author: $this->chun,
            I: $I,
        );
    }

    private function givenMagneticNetIsInactive(): void
    {
        $this->player->getDaedalus()->getNeron()->toggleMagneticNet();
    }

    private function givenMagneticNetIsActive(): void
    {
        // nothng to do here (default state)
    }

    private function whenPlayerTogglesMagneticNet(): void
    {
        $this->toggleMagneticNet->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->biosTerminal,
            player: $this->player,
            target: $this->biosTerminal,
        );
        $this->toggleMagneticNet->execute();
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->toggleMagneticNet->isVisible());
    }

    private function thenMagneticNetIsActive(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->getDaedalus()->isMagneticNetActive());
    }

    private function thenMagneticNetIsInactive(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->getDaedalus()->isMagneticNetActive());
    }

    private function thenPrivateLogIsPrinted(FunctionalTester $I): void
    {
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getLogName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->chun->getPlayerInfo(),
            'log' => ActionLogEnum::CHANGE_NERON_PARAMETER_SUCCESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
