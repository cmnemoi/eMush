<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\TogglePlasmaShield;
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
final class TogglePlasmaShieldCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private TogglePlasmaShield $togglePlasmaShield;
    private GameEquipment $biosTerminal;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TOGGLE_PLASMA_SHIELD]);
        $this->togglePlasmaShield = $I->grabService(TogglePlasmaShield::class);

        $this->givenABiosTerminalInChunRoom($I->grabService(GameEquipmentServiceInterface::class));
        $this->givenChunIsFocusedOnBiosTerminal($I->grabService(StatusServiceInterface::class));
    }

    public function shouldNotBeVisibleIfPlasmaShieldIsNotFinished(FunctionalTester $I): void
    {
        $this->givenPlasmaShieldIsActive();

        $this->whenPlayerTogglesPlasmaShield();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldActivateInactiveShield(FunctionalTester $I): void
    {
        $this->givenPlasmaShieldIsFinished($I);
        $this->givenPlasmaShieldIsInactive();

        $this->whenPlayerTogglesPlasmaShield();

        $this->thenPlasmaShieldIsActive($I);
    }

    public function shouldDeactivateActiveShield(FunctionalTester $I): void
    {
        $this->givenPlasmaShieldIsFinished($I);
        $this->givenPlasmaShieldIsActive();

        $this->whenPlayerTogglesPlasmaShield();

        $this->thenPlasmaShieldIsInactive($I);
    }

    public function shouldPrintAPrivateLog(FunctionalTester $I): void
    {
        $this->givenPlasmaShieldIsFinished($I);
        $this->givenPlasmaShieldIsInactive();

        $this->whenPlayerTogglesPlasmaShield();

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

    private function givenPlasmaShieldIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            author: $this->chun,
            I: $I,
        );
    }

    private function givenPlasmaShieldIsInactive(): void
    {
        // nothng to do here (default state)
    }

    private function givenPlasmaShieldIsActive(): void
    {
        $this->player->getDaedalus()->getNeron()->togglePlasmaShield();
    }

    private function whenPlayerTogglesPlasmaShield(): void
    {
        $this->togglePlasmaShield->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->biosTerminal,
            player: $this->player,
            target: $this->biosTerminal,
        );
        $this->togglePlasmaShield->execute();
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->togglePlasmaShield->isVisible());
    }

    private function thenPlasmaShieldIsActive(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->getDaedalus()->isPlasmaShieldActive());
    }

    private function thenPlasmaShieldIsInactive(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->getDaedalus()->isPlasmaShieldActive());
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
