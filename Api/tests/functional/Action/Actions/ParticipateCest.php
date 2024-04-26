<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Participate;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Service\NeronServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
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
    private Action $actionConfig;
    private Participate $participateAction;
    private Project $project;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private NeronServiceInterface $neronService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::PARTICIPATE]);
        $this->participateAction = $I->grabService(Participate::class);

        $this->project = $this->createProject(ProjectName::TRAIL_REDUCER, $I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->neronService = $I->grabService(NeronServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given Chun is focused on NERON's cored terminal
        $terminal = $this->gameEquipmentService->createGameEquipmentFromName(
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
            target: $terminal,
        );

        // given KT is focused on PILGRED terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
            target: $terminal,
        );
    }

    public function shouldNotBeExecutableIfPlayerEfficiencyIsEqualsToZero(FunctionalTester $I): void
    {
        // and Chun's efficiency is 0
        $this->setPlayerProjectEfficiencyToZero($this->chun, $this->project);

        // when Chun tries to participate in the project
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
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
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
        $this->participateAction->execute();

        // then the project should progress by 6 to 9%
        $I->assertGreaterThanOrEqual(6, $this->project->getProgress());
        $I->assertLessThanOrEqual(9, $this->project->getProgress());
    }

    public function shouldCreateAPrivateLog(FunctionalTester $I): void
    {
        // when Chun participates in the project
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
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
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
        $this->participateAction->execute();

        // then Chun's efficiency should be reduced to 4-6%
        $I->assertEquals(new PlayerEfficiency(4, 6), $this->chun->getEfficiencyForProject($this->project));
    }

    public function shouldNotReduceEfficiencyForOtherProjects(FunctionalTester $I): void
    {
        // and I have the plasma shield project
        $otherProject = $this->createProject(ProjectName::PLASMA_SHIELD, $I);

        // when Chun participates in the project
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
        $this->participateAction->execute();

        // then Chun's efficiency for the plasma shield project should not be reduced
        $I->assertEquals(new PlayerEfficiency(1, 1), $this->chun->getEfficiencyForProject($otherProject));
    }

    public function shouldResetOtherPlayersEfficiencyForProject(FunctionalTester $I): void
    {
        // given Chun's efficiency is 0-0%
        $this->setPlayerProjectEfficiencyToZero($this->chun, $this->project);

        // when Kuan-Ti participates in the project
        $this->participateAction->loadParameters($this->actionConfig, $this->kuanTi, $this->project);
        $this->participateAction->execute();

        // then Chun's efficiency should be reset to 6-9%
        $I->assertEquals(new PlayerEfficiency(6, 9), $this->chun->getEfficiencyForProject($this->project));
    }

    public function shouldCreateANeronAnnouncementWhenProjectIsFinished(FunctionalTester $I): void
    {
        // given PILGRED progress is 99%
        $this->project->makeProgress(99);

        // when Chun participates in the project
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
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
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
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
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
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
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
        $this->participateAction->execute();

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
            actual: $this->participateAction->cannotExecuteReason(),
        );
    }

    public function shouldReduceEfficiencyWhenParticpatingToAnotherProject(FunctionalTester $I): void
    {
        // given Chun participates in the project
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $this->project);
        $this->participateAction->execute();

        // when Chun participates in another project
        $otherProject = $this->createProject(ProjectName::PLASMA_SHIELD, $I);
        $this->participateAction->loadParameters($this->actionConfig, $this->chun, $otherProject);
        $this->participateAction->execute();

        // then Chun's efficiency for the other project should be reduced
        $I->assertEquals(new PlayerEfficiency(0, 0), $this->chun->getEfficiencyForProject($otherProject));
    }

    private function setPlayerProjectEfficiencyToZero(Player $player, Project $project): void
    {
        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::PROJECT_PARTICIPATIONS,
            holder: $player,
            tags: [],
            time: new \DateTime(),
            target: $project,
        );
        $this->statusService->updateCharge(
            chargeStatus: $status,
            delta: 10000,
            tags: [],
            time: new \DateTime(),
        );
    }
}
