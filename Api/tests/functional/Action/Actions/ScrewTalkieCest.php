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
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
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

        $this->addSkillToPlayer(SkillEnum::RADIO_PIRACY, $I);
    }

    public function shouldMakeMycoAlarmRing(FunctionalTester $I): void
    {
        $this->givenChunHasTalkie();

        $this->givenKuanTiHasTalkie();

        $this->givenMycoAlarmInRoom();

        $this->whenChunScrewsKuanTiTalkie();

        $this->thenMycoAlarmPrintsPublicLog($I);
    }

    private function givenChunHasTalkie(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ITRACKIE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiHasTalkie(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ITRACKIE,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
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

    private function whenChunScrewsKuanTiTalkie(): void
    {
        $this->screwTalkie->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->screwTalkie->execute();
    }

    private function thenMycoAlarmPrintsPublicLog(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ':mycoalarm: DRIIIIIIIIIIIIIIIIIIIIIIIIIINNNNNGGGGG!!!!',
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
