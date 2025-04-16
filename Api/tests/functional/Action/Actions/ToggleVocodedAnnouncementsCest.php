<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ToggleVocodedAnnouncements;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
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
final class ToggleVocodedAnnouncementsCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ToggleVocodedAnnouncements $toggleVocodedAnnouncements;
    private GameEquipment $biosTerminal;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TOGGLE_VOCODED_ANNOUNCEMENTS]);
        $this->toggleVocodedAnnouncements = $I->grabService(ToggleVocodedAnnouncements::class);

        $this->givenABiosTerminalInChunRoom($I->grabService(GameEquipmentServiceInterface::class));
        $this->givenChunIsFocusedOnBiosTerminal($I->grabService(StatusServiceInterface::class));
    }

    public function shouldActivateInactiveVocodedAnnouncements(FunctionalTester $I): void
    {
        $this->givenVocodedAnnouncementsAreInactive();

        $this->whenPlayerTogglesVocodedAnnouncements();

        $this->thenVocodedAnnouncementsAreActive($I);
    }

    public function shouldDeactivateActiveVocodedAnnouncements(FunctionalTester $I): void
    {
        $this->givenVocodedAnnouncementsAreActive();

        $this->whenPlayerTogglesVocodedAnnouncements();

        $this->thenVocodedAnnouncementsAreInactive($I);
    }

    public function shouldPrintAPrivateLog(FunctionalTester $I): void
    {
        $this->givenVocodedAnnouncementsAreInactive();

        $this->whenPlayerTogglesVocodedAnnouncements();

        $this->thenPrivateLogIsPrinted($I);
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

    private function givenVocodedAnnouncementsAreInactive(): void
    {
        // nothng to do here (default state)
    }

    private function givenVocodedAnnouncementsAreActive(): void
    {
        $this->player->getDaedalus()->getNeron()->toggleVocodedAnnouncements();
    }

    private function whenPlayerTogglesVocodedAnnouncements(): void
    {
        $this->toggleVocodedAnnouncements->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->biosTerminal,
            player: $this->player,
            target: $this->biosTerminal,
        );
        $this->toggleVocodedAnnouncements->execute();
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->toggleVocodedAnnouncements->isVisible());
    }

    private function thenVocodedAnnouncementsAreActive(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->getDaedalus()->getNeron()->areVocodedAnnouncementsActive(), 'Vocoded announcements should be active');
    }

    private function thenVocodedAnnouncementsAreInactive(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->getDaedalus()->getNeron()->areVocodedAnnouncementsActive(), 'Vocoded announcements should be inactive');
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
