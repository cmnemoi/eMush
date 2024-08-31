<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ToggleNeronInhibition;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ToggleNeronInhibitionCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ToggleNeronInhibition $toggleNeronInhibition;
    private GameEquipment $biosTerminal;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TOGGLE_NERON_INHIBITION]);
        $this->toggleNeronInhibition = $I->grabService(ToggleNeronInhibition::class);

        $this->givenABiosTerminalInChunRoom($I->grabService(GameEquipmentServiceInterface::class));
        $this->givenChunIsFocusedOnBiosTerminal($I->grabService(StatusServiceInterface::class));
    }

    public function shouldInhibitNeron(FunctionalTester $I): void
    {
        $this->givenNeronIsNotInhibited();

        $this->whenPlayerTogglesNeronInhibition();

        $this->thenNeronIsInhibited($I);
    }

    public function shouldUninhibitNeron(FunctionalTester $I): void
    {
        $this->givenNeronIsInhibited();

        $this->whenPlayerTogglesNeronInhibition();

        $this->thenNeronIsNotInhibited($I);
    }

    public function shouldPrintAPrivateLog(FunctionalTester $I): void
    {
        $this->givenNeronIsNotInhibited();

        $this->whenPlayerTogglesNeronInhibition();

        $this->thenPrivateLogIsPrinted($I);
    }

    public function shouldMakeANeronAnnouncementOnInhibition(FunctionalTester $I): void
    {
        $this->givenNeronIsNotInhibited();

        $this->whenPlayerTogglesNeronInhibition();

        $this->thenNeronAnnouncementIsPrinted(NeronMessageEnum::ACTIVATE_DMZ, $I);
    }

    public function shouldMakeANeronAnnouncementOnUninhibition(FunctionalTester $I): void
    {
        $this->givenNeronIsInhibited();

        $this->whenPlayerTogglesNeronInhibition();

        $this->thenNeronAnnouncementIsPrinted(NeronMessageEnum::DEACTIVATE_DMZ, $I);
    }

    private function givenABiosTerminalInChunRoom(GameEquipmentServiceInterface $gameEquipmentService): void
    {
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

    private function givenNeronIsNotInhibited(): void
    {
        $this->player->getDaedalus()->getNeron()->toggleInhibition();
    }

    private function givenNeronIsInhibited(): void
    {
        // nothng to do here (default state)
    }

    private function whenPlayerTogglesNeronInhibition(): void
    {
        $this->toggleNeronInhibition->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->biosTerminal,
            player: $this->player,
            target: $this->biosTerminal,
        );
        $this->toggleNeronInhibition->execute();
    }

    private function thenNeronIsInhibited(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->getDaedalus()->getNeron()->isInhibited());
    }

    private function thenNeronIsNotInhibited(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->getDaedalus()->getNeron()->isInhibited());
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

    private function thenNeronAnnouncementIsPrinted(string $message, FunctionalTester $I): void
    {
        $I->seeInRepository(Message::class, [
            'neron' => $this->daedalus->getNeron(),
            'message' => $message,
        ]);
    }
}
