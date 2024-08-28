<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\AcceptMission;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Exception\GameException;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AcceptMissionCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private AcceptMission $acceptMission;
    private CommanderMission $mission;
    private AddSkillToPlayerService $addSkillToPlayer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::ACCEPT_MISSION]);
        $this->acceptMission = $I->grabService(AcceptMission::class);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);

        $this->givenChunSendsAMissionToKuanTi($I);
    }

    public function shouldGiveThreeActionPointsToPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(0);

        $this->whenKuanTiAcceptsTheMission();

        $this->thenKuanTiShouldHaveActionPoints(3, $I);
    }

    public function shouldMarkMissionAsNonPending(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(0);

        $this->whenKuanTiAcceptsTheMission();

        $I->assertFalse($this->mission->isPending());
    }

    public function shouldThrowIfMissionIsNotPending(FunctionalTester $I): void
    {
        $this->givenChunSendsAMissionToKuanTi($I);

        $this->givenMissionHasBeenAccepted();

        $I->expectThrowable(GameException::class, function () {
            $this->whenKuanTiAcceptsTheMission();
        });
    }

    public function shouldThrowIfMissionNotAddressedToPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiSendsAMissionToChun($I);

        $I->expectThrowable(GameException::class, function () {
            $this->whenChunAcceptsKuanTiMission();
        });
    }

    public function shouldAddMissionAcceptedNotificationToCommander(FunctionalTester $I): void
    {
        $this->whenKuanTiAcceptsTheMission();

        $I->seeInRepository(PlayerNotification::class, [
            'player' => $this->chun,
            'message' => PlayerNotificationEnum::MISSION_ACCEPTED->toString(),
        ]);
    }

    public function shouldNotBeVisibleIfPlayerDoesNotHavePendingMissions(FunctionalTester $I): void
    {
        $this->givenMissionHasBeenAccepted();

        $this->whenKuanTiTriesToAcceptTheMission();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldGiveMoreActionPointsToDevotedPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(0);

        $this->givenKuanTiHasDevotionSkill();

        $this->whenKuanTiAcceptsTheMission();

        $this->thenKuanTiShouldHaveActionPoints(6, $I);
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

    private function givenMissionHasBeenAccepted(): void
    {
        $this->mission->accept();
    }

    private function givenKuanTiHasDevotionSkill(): void
    {
        $this->addSkillToPlayer->execute(skill: SkillEnum::DEVOTION, player: $this->kuanTi);
    }

    private function whenKuanTiAcceptsTheMission(): void
    {
        $this->acceptMission->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
            parameters: ['missionId' => $this->mission->getId()],
        );
        $this->acceptMission->execute();
    }

    private function whenChunAcceptsKuanTiMission(): void
    {
        $this->acceptMission->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: null,
            parameters: ['missionId' => $this->mission->getId()],
        );
        $this->acceptMission->execute();
    }

    private function whenKuanTiTriesToAcceptTheMission(): void
    {
        $this->acceptMission->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
            parameters: [],
        );
    }

    private function thenKuanTiShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->kuanTi->getActionPoint());
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->acceptMission->isVisible());
    }
}
