<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\TrapCloset;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class TrapClosetCest extends AbstractFunctionalTest
{
    private ActionConfig $trapClosetConfig;
    private TrapCloset $trapClosetAction;

    private AddSkillToPlayerService $addSkillToPlayer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->trapClosetConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TRAP_CLOSET]);
        $this->trapClosetAction = $I->grabService(TrapCloset::class);
        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given KT is Mush so he has the action available
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
        $this->addSkillToPlayer->execute(SkillEnum::TRAPPER, $this->kuanTi);
    }

    public function shouldNotBeVisibleIfPlayerDoesNotHaveASporeAvailable(FunctionalTester $I): void
    {
        // given KT has no spore
        $I->assertEquals(
            expected: 0,
            actual: $this->kuanTi->getSpores(),
        );

        // when KT tries to trap the closet
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );

        // then the action should not be visible
        $I->assertFalse($this->trapClosetAction->isVisible());
    }

    public function shouldNotBeExecutableInNonRoomPlace(FunctionalTester $I): void
    {
        // given KT has one spore
        $this->kuanTi->setSpores(1);

        // given KT is in the space
        $this->kuanTi->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));

        // when KT tries to trap the closet
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::NOT_A_ROOM,
            actual: $this->trapClosetAction->cannotExecuteReason(),
        );
    }

    public function shouldConsumeOnePlayerSpore(FunctionalTester $I): void
    {
        // given KT has one spore
        $this->kuanTi->setSpores(1);

        // when KT traps the closet
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );
        $this->trapClosetAction->execute();

        // then KT should have no spores left
        $I->assertEquals(
            expected: 0,
            actual: $this->kuanTi->getSpores(),
        );
    }

    public function shouldAddATrappedStatusToPlayerRoom(FunctionalTester $I): void
    {
        // given KT has one spore
        $this->kuanTi->setSpores(1);

        // when KT traps the closet
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );
        $this->trapClosetAction->execute();

        // then the room should have a trapped status
        $I->assertTrue($this->kuanTi->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));
    }

    public function shouldNotBeExecutableIfRoomIsAlreadyTrapped(FunctionalTester $I): void
    {
        // given KT has one spore
        $this->kuanTi->setSpores(1);

        // given KT's room has been trapped already
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

        // when KT tries to trap the closet
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::BOOBY_TRAP_ALREADY_DONE,
            actual: $this->trapClosetAction->cannotExecuteReason(),
        );
    }

    public function shouldPrintASecretLog(FunctionalTester $I): void
    {
        // given Chun is another room to not reveal the secret log
        $this->chun->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));

        // given KT has one spore
        $this->kuanTi->setSpores(1);

        // when KT traps the closet
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );
        $this->trapClosetAction->execute();

        // then the room should have a trapped status
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->kuanTi->getPlace()->getLogName(),
                'playerInfo' => $this->kuanTi->getPlayerInfo(),
                'visibility' => VisibilityEnum::SECRET,
                'log' => ActionLogEnum::TRAP_CLOSET_SUCCESS,
            ],
        );
    }

    public function shouldMakeMycoAlarmRing(FunctionalTester $I): void
    {
        $this->givenMycoAlarmInRoom();

        $this->givenPlayerHasSpores(1);

        $this->whenPlayerTrapsTheCloset();

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

    private function givenPlayerHasSpores(int $spores): void
    {
        $this->kuanTi->setSpores($spores);
    }

    private function whenPlayerTrapsTheCloset(): void
    {
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );
        $this->trapClosetAction->execute();
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
