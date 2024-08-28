<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\RejectMission;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Exception\GameException;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RejectMissionCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private RejectMission $rejectMission;
    private CommanderMission $mission;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REJECT_MISSION]);
        $this->rejectMission = $I->grabService(RejectMission::class);

        $this->givenChunSendsAMissionToKuanTi($I);
    }

    public function shouldMarkMissionAsNonPending(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(0);

        $this->whenKuanTiRejectsTheMission();

        $I->assertFalse($this->mission->isPending());
    }

    public function shouldThrowIfMissionIsNotPending(FunctionalTester $I): void
    {
        $this->givenChunSendsAMissionToKuanTi($I);

        $this->givenMissionHasBeenRejected();

        $I->expectThrowable(GameException::class, function () {
            $this->whenKuanTiRejectsTheMission();
        });
    }

    public function shouldThrowIfMissionNotAddressedToPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiSendsAMissionToChun($I);

        $I->expectThrowable(GameException::class, function () {
            $this->whenChunRejectsKuanTiMission();
        });
    }

    public function shouldAddMissionRejectedNotificationToCommander(FunctionalTester $I): void
    {
        $this->whenKuanTiRejectsTheMission();

        $I->seeInRepository(PlayerNotification::class, [
            'player' => $this->chun,
            'message' => PlayerNotificationEnum::MISSION_REJECTED->toString(),
        ]);
    }

    public function shouldNotBeVisibleIfPlayerDoesNotHavePendingMissions(FunctionalTester $I): void
    {
        $this->givenMissionHasBeenRejected();

        $this->whenKuanTiTriesToRejectTheMission();

        $this->thenActionIsNotVisible($I);
    }

    private function givenChunSendsAMissionToKuanTi(FunctionalTester $I): void
    {
        $this->mission = new CommanderMission(
            commander: $this->chun,
            subordinate: $this->kuanTi,
            mission: 'Mission',
        );
        $I->haveInRepository($this->mission);
    }

    private function givenKuanTiSendsAMissionToChun(FunctionalTester $I): void
    {
        $mission = new CommanderMission(
            commander: $this->kuanTi,
            subordinate: $this->chun,
            mission: 'Mission',
        );
        $I->haveInRepository($mission);
    }

    private function givenKuanTiHasActionPoints(int $actionPoints): void
    {
        $this->kuanTi->setActionPoint($actionPoints);
    }

    private function givenMissionHasBeenRejected(): void
    {
        $this->mission->reject();
    }

    private function whenKuanTiRejectsTheMission(): void
    {
        $this->rejectMission->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            parameters: ['missionId' => $this->mission->getId()],
        );
        $this->rejectMission->execute();
    }

    private function whenChunRejectsKuanTiMission(): void
    {
        $this->rejectMission->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            parameters: ['missionId' => $this->mission->getId()],
        );
        $this->rejectMission->execute();
    }

    private function whenKuanTiTriesToRejectTheMission(): void
    {
        $this->rejectMission->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->rejectMission->isVisible());
    }
}
