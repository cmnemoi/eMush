<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\RepairPilgred;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RepairPilgredCest extends AbstractFunctionalTest
{
    private Action $actionConfig;
    private RepairPilgred $repairPilgredAction;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(Action::class, ['name' => 'repair_pilgred']);
        $this->repairPilgredAction = $I->grabService(RepairPilgred::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given Chun is focused on PILGRED terminal
        $terminal = $I->grabService(GameEquipmentServiceInterface::class)->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PILGRED,
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
    }

    public function shouldNotBeExecutableIfPlayerEfficiencyIsEqualsToZero(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->createProject(ProjectName::PILGRED, $I);

        // and Chun's efficiency is 0
        $this->setPlayerProjectEfficiencyToZero($this->chun, $pilgredProject, $I);

        // when Chun tries to repair the PILGRED project
        $this->repairPilgredAction->loadParameters($this->actionConfig, $this->chun, $pilgredProject);
        $this->repairPilgredAction->execute($this->chun, $pilgredProject);

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::NO_EFFICIENCY,
            actual: $this->repairPilgredAction->cannotExecuteReason(),
        );
    }

    public function shouldMakePilgredProgress(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->createProject(ProjectName::PILGRED, $I);

        // when Chun repairs the PILGRED project
        $this->repairPilgredAction->loadParameters($this->actionConfig, $this->chun, $pilgredProject);
        $this->repairPilgredAction->execute($this->chun, $pilgredProject);

        // then the PILGRED project should progress by 1
        $I->assertEquals(1, $pilgredProject->getProgress());
    }

    public function shouldCreateAPublicLog(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->createProject(ProjectName::PILGRED, $I);

        // when Chun repairs the PILGRED project
        $this->repairPilgredAction->loadParameters($this->actionConfig, $this->chun, $pilgredProject);
        $this->repairPilgredAction->execute($this->chun, $pilgredProject);

        // then a public log should be created in Chun's room
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getLogName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'visibility' => VisibilityEnum::PUBLIC,
            'log' => ActionLogEnum::REPAIR_PILGRED_SUCCESS,
        ]);
    }

    public function shouldReducePlayerEfficiencyForProject(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->createProject(ProjectName::PILGRED, $I);

        // when Chun repairs the PILGRED project
        $this->repairPilgredAction->loadParameters($this->actionConfig, $this->chun, $pilgredProject);
        $this->repairPilgredAction->execute($this->chun, $pilgredProject);

        // then Chun's efficiency should be reduced to 0
        $I->assertEquals(0, $this->chun->getMinEfficiencyForProject($pilgredProject));
    }

    private function setPlayerProjectEfficiencyToZero(Player $player, Project $project, FunctionalTester $I): void
    {
        $status = $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::PROJECT_PARTICIPATIONS,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
        $this->statusService->updateCharge(
            chargeStatus: $status,
            delta: PHP_INT_MAX,
            tags: [],
            time: new \DateTime(),
        );
    }
}
