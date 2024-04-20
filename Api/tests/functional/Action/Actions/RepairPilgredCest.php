<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\RepairPilgred;
use Mush\Action\Entity\Action;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RepairPilgredCest extends AbstractFunctionalTest
{
    private Action $actionConfig;
    private RepairPilgred $repairPilgredAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(Action::class, ['name' => 'repair_pilgred']);
        $this->repairPilgredAction = $I->grabService(RepairPilgred::class);
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
}
