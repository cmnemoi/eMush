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
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\ValueObject\PlayerEfficiency;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\ChargeStatus;
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
    private GameEquipmentServiceInterface $gameEquipmentService;
    private GameEquipment $terminal;
    private NeronServiceInterface $neronService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PARTICIPATE]);
        $this->participateAction = $I->grabService(Participate::class);

        $this->project = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $this->project->propose();

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
        $this->project->makeProgress(99);

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
        $this->project->makeProgress(100);

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
        $this->project->makeProgress(99);

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
        $this->project->makeProgress(99);

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

    public function shouldConsumeCoreSpecialistPointWithProject(FunctionalTester $I): void
    {
        // given I have another proposed project
        $project = $this->daedalus->getProjectByName(ProjectName::AUXILIARY_TERMINAL);
        $project->propose();

        // given Chun is a Conceptor
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::CONCEPTOR,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given Chun has one Core point
        /** @var ChargeStatus $skill */
        $skill = $this->chun->getSkillByName(SkillEnum::CONCEPTOR);
        $skill->setCharge(1);

        // when Chun participates in the project
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
        $this->participateAction->execute();

        // then one of Chun's Core points is consumed
        $I->assertEquals(0, $skill->getCharge());
    }

    private function setPlayerProjectEfficiencyToZero(Player $player, Project $project): void
    {
        for ($i = 0; $i < $player->getEfficiencyForProject($project)->max; ++$i) {
            $project->addPlayerParticipation($player);
        }
    }
}
