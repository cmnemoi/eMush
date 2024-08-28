<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\CommanderOrder;
use Mush\Action\Actions\RejectMission;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Exception\GameException;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CommanderOrderCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private CommanderOrder $commanderOrder;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::COMMANDER_ORDER]);
        $this->commanderOrder = $I->grabService(CommanderOrder::class);

        $this->givenChunIsCommander();
    }

    public function shouldNotBeVisibleIfPlayerIsNotCommander(FunctionalTester $I): void
    {
        $this->whenKuanTiTriesToOrderAMissionToChun();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldCreateCommanderMissionToSubordinate(FunctionalTester $I): void
    {
        $this->whenChunOrderAMissionToKuanTi();

        $this->thenKuanTiShouldHaveAMission($I);
    }

    public function shouldThrowIfTryingToSendMissionToNonContactablePlayer(FunctionalTester $I): void
    {
        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);

        $this->givenKuanTiIsInFrontCorridorWithoutMeansOfCommunication($I);

        $I->expectThrowable(GameException::class, function () {
            $this->whenChunOrderAMissionToKuanTi();
        });
    }

    public function shouldCreateNotificationForCommander(FunctionalTester $I): void
    {
        $this->whenChunOrderAMissionToKuanTi();

        $this->thenChunShouldHaveNotification($I);
    }

    public function shouldCreateNotificationForSubordinate(FunctionalTester $I): void
    {
        $this->whenChunOrderAMissionToKuanTi();

        $this->thenKuanTiShouldHaveNotification($I);
    }

    public function shouldNotBeExecutableIfAlreadyDoneToday(FunctionalTester $I): void
    {
        $this->givenChunOrderAMissionToKuanTi();

        $this->whenChunTriesToOrderAMissionToKuanTi();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::ISSUE_MISSION_ALREADY_ISSUED, $I);
    }

    public function shouldBeExecutableIfSubordinateRefusesMission(FunctionalTester $I): void
    {
        $this->givenChunOrderAMissionToKuanTi();

        $this->givenKuanTiRefusesMission($I);

        $this->whenChunTriesToOrderAMissionToKuanTi();

        $this->thenActionIsExecutable($I);
    }

    public function shouldNotBeExecutableIfThereIsNoContactablePlayers(FunctionalTester $I): void
    {
        $this->givenKuanTiIsInFrontCorridorWithoutMeansOfCommunication($I);

        $this->whenChunTriesToOrderAMissionToKuanTi();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::ISSUE_MISSION_NO_TARGET, $I);
    }

    private function givenChunIsCommander(): void
    {
        $this->chun->addTitle(TitleEnum::COMMANDER);
    }

    private function givenKuanTiIsInFrontCorridorWithoutMeansOfCommunication(FunctionalTester $I): void
    {
        $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $this->kuanTi->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::FRONT_CORRIDOR));
    }

    private function givenChunOrderAMissionToKuanTi(): void
    {
        $this->commanderOrder->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            parameters: ['subordinate' => $this->kuanTi->getLogName(), 'mission' => 'test'],
        );
        $this->commanderOrder->execute();
    }

    private function givenKuanTiRefusesMission(FunctionalTester $I): void
    {
        $refuseMissionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REJECT_MISSION]);
        $refuseMission = $I->grabService(RejectMission::class);

        $refuseMission->loadParameters(
            actionConfig: $refuseMissionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            parameters: ['missionId' => $this->kuanTi->getReceivedMissions()->first()->getId()],
        );
        $refuseMission->execute();
    }

    private function whenKuanTiTriesToOrderAMissionToChun(): void
    {
        $this->commanderOrder->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            parameters: ['subordinate' => $this->chun->getLogName(), 'mission' => 'test'],
        );
    }

    private function whenChunOrderAMissionToKuanTi(): void
    {
        $this->commanderOrder->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            parameters: ['subordinate' => $this->kuanTi->getLogName(), 'mission' => 'test'],
        );
        $this->commanderOrder->execute();
    }

    private function whenChunTriesToOrderAMissionToKuanTi(): void
    {
        $this->commanderOrder->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            parameters: ['subordinate' => $this->kuanTi->getLogName(), 'mission' => 'test'],
        );
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->commanderOrder->isVisible());
    }

    private function thenKuanTiShouldHaveAMission(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->kuanTi->getReceivedMissions());
    }

    private function thenChunShouldHaveNotification(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: PlayerNotificationEnum::MISSION_SENT->toString(),
            actual: $this->chun->getNotificationOrThrow()->getMessage(),
        );
    }

    private function thenKuanTiShouldHaveNotification(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: PlayerNotificationEnum::MISSION_RECEIVED->toString(),
            actual: $this->kuanTi->getNotificationOrThrow()->getMessage(),
        );
    }

    private function thenActionIsNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->commanderOrder->cannotExecuteReason(),
        );
    }

    private function thenActionIsExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->commanderOrder->cannotExecuteReason());
    }
}
