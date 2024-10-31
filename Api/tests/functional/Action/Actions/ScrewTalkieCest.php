<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ScrewTalkie;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

final class ScrewTalkieCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ScrewTalkie $screwTalkie;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SCREW_TALKIE]);
        $this->screwTalkie = $I->grabService(ScrewTalkie::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldMakeMycoAlarmRing(FunctionalTester $I): void
    {
        // Given there is a myco alarm in the room
        $this->givenMycoAlarmInRoom();

        // When player executes the screw talkie action
        $this->whenPlayerScrewsTalkie();

        // Then myco alarm should print a public log
        $this->thenMycoAlarmPrintsPublicLog($I);
    }

    private function givenMycoAlarmInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MYCO_ALARM,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerScrewsTalkie(): void
    {
        $this->screwTalkie->loadParameters(
            actionConfig: $this->actionConfig,
            player: $this->player,
        );
        $this->screwTalkie->execute();
    }

    private function thenMycoAlarmPrintsPublicLog(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'DRIIIIIIIIIIIIIIIIIIIIIIIIIINNNNNGGGGG!!!!',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::MYCO_ALARM_RING,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }
}
