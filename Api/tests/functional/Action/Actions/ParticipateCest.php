<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Participate;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Service\NeronServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\ValueObject\PlayerEfficiency;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ParticipateCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Participate $participateAction;
    private Project $project;
    private GameEquipment $terminal;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private NeronServiceInterface $neronService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PARTICIPATE]);
        $this->participateAction = $I->grabService(Participate::class);

        $this->project = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $this->project->propose();

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->neronService = $I->grabService(NeronServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given Chun is focused on NERON's cored terminal
        $this->terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->terminal,
        );

        // given KT is focused on PILGRED terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
            target: $this->terminal,
        );
    }

    public function shouldNotBeExecutableIfPlayerEfficiencyIsEqualsToZero(FunctionalTester $I): void
    {
        // and Chun's efficiency is 0
        $this->setPlayerProjectEfficiencyToZero($this->chun, $this->project);

        // when Chun tries to participate in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::NO_EFFICIENCY,
            actual: $this->participateAction->cannotExecuteReason(),
        );
    }

    public function shouldMakeProjectProgress(FunctionalTester $I): void
    {
        // when Chun participates in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then the project should progress by 6 to 9%
        $I->assertGreaterThanOrEqual(6, $this->project->getProgress());
        $I->assertLessThanOrEqual(9, $this->project->getProgress());
    }

    public function shouldCreateAPrivateLog(FunctionalTester $I): void
    {
        // when Chun participates in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then a private log should be created in Chun's room
        /** @var RoomLog $log */
        $log = $I->grabEntityFromRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getLogName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::PARTICIPATE_SUCCESS,
        ]);

        // then this log should have the project name as a parameter
        $I->assertEquals('trail_reducer', $log->getParameters()['target_project']);

        // then it should contain NERON mood
        $I->assertArrayHasKey('neronMood', $log->getParameters());
    }

    public function shouldReducePlayerEfficiencyForProject(FunctionalTester $I): void
    {
        // when Chun participates in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then Chun's efficiency should be reduced to 4-6%
        $I->assertEquals(new PlayerEfficiency(4, 6), $this->chun->getEfficiencyForProject($this->project));
    }

    public function shouldNotReduceEfficiencyForOtherProjects(FunctionalTester $I): void
    {
        // and I have the plasma shield project
        $otherProject = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);

        // when Chun participates in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then Chun's efficiency for the plasma shield project should not be reduced
        $I->assertEquals(new PlayerEfficiency(1, 1), $this->chun->getEfficiencyForProject($otherProject));
    }

    public function shouldResetOtherPlayersEfficiencyForProject(FunctionalTester $I): void
    {
        // given Chun's efficiency is 0-0%
        $this->setPlayerProjectEfficiencyToZero($this->chun, $this->project);

        // when Kuan-Ti participates in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->kuanTi,
            target: $this->project
        );
        $this->participateAction->execute();

        // then Chun's efficiency should be reset to 6-9%
        $I->assertEquals(new PlayerEfficiency(6, 9), $this->chun->getEfficiencyForProject($this->project));
    }

    public function shouldCreateANeronAnnouncementWhenProjectIsFinished(FunctionalTester $I): void
    {
        // given PILGRED progress is 99%
        $this->project->makeProgressAndUpdateParticipationDate(99);

        // when Chun participates in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then a Neron announcement should be created
        $neronAnnouncement = $I->grabEntityFromRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::NEW_PROJECT],
        );
        $I->assertEquals($neronAnnouncement->getTranslationParameters()['character'], 'chun');
        $I->assertEquals($neronAnnouncement->getTranslationParameters()['project'], 'trail_reducer');
    }

    public function shouldNotBeVisibleIfProjectIsFinished(FunctionalTester $I): void
    {
        // given project progress is 100%
        $this->project->makeProgressAndUpdateParticipationDate(100);

        // when Chun tries to participate in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then the action should not be visible
        $I->assertFalse($this->participateAction->isVisible());
    }

    public function shouldPutEfficiencyToFiveSevenPercentsWithCpuPriority(FunctionalTester $I): void
    {
        // given CPU priority is set in the project
        $this->neronService->changeCpuPriority(
            neron: $this->daedalus->getDaedalusInfo()->getNeron(),
            cpuPriority: NeronCpuPriorityEnum::PROJECTS,
        );

        // when Chun participates in the project with CPU priority
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then Chun's efficiency should be 5-7%
        $I->assertEquals(new PlayerEfficiency(5, 7), $this->chun->getEfficiencyForProject($this->project));
    }

    public function shouldNotBeExecutableIfPlayerIsDirty(FunctionalTester $I): void
    {
        // given Chun is dirty
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when Chun tries to participate in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
            actual: $this->participateAction->cannotExecuteReason(),
        );
    }

    public function shouldReduceEfficiencyWhenParticipatingToAnotherProject(FunctionalTester $I): void
    {
        // given Chun participates in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // when Chun participates in another project
        $otherProject = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $otherProject
        );
        $I->assertTrue($this->participateAction->isVisible());
        $I->assertNull($this->participateAction->cannotExecuteReason());

        $this->participateAction->execute();

        // then Chun's efficiency for the other project should be reduced
        $I->assertEquals(new PlayerEfficiency(0, 0), $this->chun->getEfficiencyForProject($otherProject));
    }

    public function shouldMakeCurrentProjectsUnproposedWhenAProjectIsFinished(FunctionalTester $I): void
    {
        // given I have another proposed project
        $otherProject = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $otherProject->propose();

        // given project progress is 99%
        $this->project->makeProgressAndUpdateParticipationDate(99);

        // when Chun participates in the project to finish it
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then project should be unproposed
        $I->assertFalse($this->project->isProposed());

        // then other project should be unproposed
        $I->assertFalse($otherProject->isProposed());
    }

    public function shouldProposeNewProjectsWhenAProjectIsFinished(FunctionalTester $I): void
    {
        // given PILGRED progress is 99%
        $this->project->makeProgressAndUpdateParticipationDate(99);

        // when Chun participates in the project to finish it
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $this->project
        );
        $this->participateAction->execute();

        // then new NERON projects should be proposed
        $I->assertCount(
            expectedCount: $this->daedalus->getNumberOfProjectsByBatch(),
            haystack: $this->daedalus->getProposedNeronProjects(),
        );
    }

    public function shouldConsumeCoreSkillPointWithProject(FunctionalTester $I): void
    {
        // given I have another proposed project
        $project = $this->daedalus->getProjectByName(ProjectName::AUXILIARY_TERMINAL);
        $project->propose();

        // given KT is a Conceptor
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::CONCEPTOR, $this->kuanTi));

        $conceptorSkill = $this->kuanTi->getSkillByNameOrThrow(SkillEnum::CONCEPTOR);

        // given KT has 4 Core points
        $I->assertEquals(
            expected: 4,
            actual: $conceptorSkill->getSkillPoints(),
        );

        // when KT participates in the project
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->kuanTi,
            target: $this->project
        );
        $this->participateAction->execute();

        // then KT should have 3 Core points
        $I->assertEquals(
            expected: 3,
            actual: $conceptorSkill->getSkillPoints(),
        );
    }

    public function itExpertShouldNotConsumeActionPoints(FunctionalTester $I): void
    {
        $this->givenKuanTiIsAnITExpert($I);

        $this->givenKuanTiHasTenActionPoints();

        $this->whenKuanToParticipatesInProject($I);

        $this->thenKuanTiShouldHaveTenActionPoints($I);
    }

    public function itExpertShouldUseOneITPoint(FunctionalTester $I): void
    {
        $this->givenKuanTiIsAnITExpert($I);

        $this->givenPlayerHasFourITPoints($I);

        $this->whenKuanToParticipatesInProject($I);

        $this->thenKuanTiShouldHaveITPoints(3, $I);
    }

    public function itExpertConceptorShouldUseOneCorePoint(FunctionalTester $I): void
    {
        $this->givenKuanTiIsAnITExpertConceptor($I);

        $this->givenPlayerHasFourCorePoints($I);

        $this->whenKuanToParticipatesInProject($I);

        $this->thenKuanTiShouldHaveCorePoints(3, $I);
    }

    public function itExpertConceptorShouldNotUseITPoint(FunctionalTester $I): void
    {
        $this->givenKuanTiIsAnITExpertConceptor($I);

        $this->givenPlayerHasFourITPoints($I);

        $this->whenKuanToParticipatesInProject($I);

        $this->thenKuanTiShouldHaveITPoints(4, $I);
    }

    public function playerWithGeniusIdeaStatusShouldFinishProjectImmediately(FunctionalTester $I): void
    {
        $this->givenPlayerHasGeniusIdeaStatus();

        $this->givenProjectProgressIs(0);

        $this->whenPlayerParticipatesInProject();

        $this->thenProjectShouldBeFinished($I);
    }

    private function givenKuanTiIsAnITExpert(FunctionalTester $I): void
    {
        $this->kuanTi->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::IT_EXPERT]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::IT_EXPERT, $this->kuanTi));
    }

    private function givenKuanTiIsAnITExpertConceptor(FunctionalTester $I): void
    {
        $this->kuanTi->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::IT_EXPERT]),
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::CONCEPTOR]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::IT_EXPERT, $this->kuanTi));
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::CONCEPTOR, $this->kuanTi));
    }

    private function givenKuanTiHasTenActionPoints(): void
    {
        $this->kuanTi->setActionPoint(10);
    }

    private function givenPlayerHasFourITPoints(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: 4,
            actual: $this->kuanTi->getSkillByNameOrThrow(SkillEnum::IT_EXPERT)->getSkillPoints(),
        );
    }

    private function givenPlayerHasFourCorePoints(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: 4,
            actual: $this->kuanTi->getSkillByNameOrThrow(SkillEnum::CONCEPTOR)->getSkillPoints(),
        );
    }

    private function givenPlayerHasGeniusIdeaStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GENIUS_IDEA,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenProjectProgressIs(int $progress): void
    {
        $this->project->makeProgress($progress);
    }

    private function whenKuanToParticipatesInProject(): void
    {
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->kuanTi,
            target: $this->project
        );
        $this->participateAction->execute();
    }

    private function whenPlayerParticipatesInProject(): void
    {
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->player,
            target: $this->project
        );
        $this->participateAction->execute();
    }

    private function thenKuanTiShouldHaveTenActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: 10,
            actual: $this->kuanTi->getActionPoint(),
        );
    }

    private function thenKuanTiShouldHaveITPoints(int $itPoints, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $itPoints,
            actual: $this->kuanTi->getSkillByNameOrThrow(SkillEnum::IT_EXPERT)->getSkillPoints(),
        );
    }

    private function thenKuanTiShouldHaveCorePoints(int $corePoints, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $corePoints,
            actual: $this->kuanTi->getSkillByNameOrThrow(SkillEnum::CONCEPTOR)->getSkillPoints(),
        );
    }

    private function thenProjectShouldBeFinished(FunctionalTester $I): void
    {
        $I->assertTrue($this->project->isFinished());
    }

    private function setPlayerProjectEfficiencyToZero(Player $player, Project $project): void
    {
        for ($i = 0; $i < $player->getEfficiencyForProject($project)->max; ++$i) {
            $project->addPlayerParticipation($player);
        }
    }
}
