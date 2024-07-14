<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\RepairPilgred;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
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
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RepairPilgredCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private RepairPilgred $repairPilgredAction;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameEquipment $terminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::REPAIR_PILGRED]);
        $this->repairPilgredAction = $I->grabService(RepairPilgred::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given Chun is focused on PILGRED terminal
        $this->terminal = $this->gameEquipmentService->createGameEquipmentFromName(
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
        // given I have the PILGRED project
        $pilgredProject = $this->daedalus->getPilgred();

        // and Chun's efficiency is 0
        $this->setPlayerProjectEfficiencyToZero($this->chun, $pilgredProject);

        // when Chun tries to repair the PILGRED project
        $this->repairPilgredAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $pilgredProject
        );
        $this->repairPilgredAction->execute();

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::NO_EFFICIENCY,
            actual: $this->repairPilgredAction->cannotExecuteReason(),
        );
    }

    public function shouldMakePilgredProgress(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->daedalus->getPilgred();

        // when Chun repairs the PILGRED project
        $this->repairPilgredAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $pilgredProject
        );
        $this->repairPilgredAction->execute();

        // then the PILGRED project should progress by 1
        $I->assertEquals(1, $pilgredProject->getProgress());
    }

    public function shouldCreateAPublicLog(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->daedalus->getPilgred();

        // when Chun repairs the PILGRED project
        $this->repairPilgredAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $pilgredProject
        );
        $this->repairPilgredAction->execute();

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
        $pilgredProject = $this->daedalus->getPilgred();

        // when Chun repairs the PILGRED project
        $this->repairPilgredAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $pilgredProject
        );
        $this->repairPilgredAction->execute();

        // then Chun's efficiency should be reduced to 0
        $I->assertEquals(new PlayerEfficiency(0, 0), $this->chun->getEfficiencyForProject($pilgredProject));
    }

    public function shouldNotReduceEfficiencyForOtherProjects(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->daedalus->getPilgred();

        // and I have the plasma shield project
        $otherProject = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);

        // when Chun repairs the PILGRED project
        $this->repairPilgredAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $pilgredProject
        );
        $this->repairPilgredAction->execute();

        // then Chun's efficiency for the plasma shield project should not be reduced
        $I->assertEquals(new PlayerEfficiency(1, 1), $this->chun->getEfficiencyForProject($otherProject));
    }

    public function shouldResetOtherPlayersEfficiencyForProject(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->daedalus->getPilgred();

        // given Chun's efficiency is 0
        $this->setPlayerProjectEfficiencyToZero($this->chun, $pilgredProject);

        // when Kuan-Ti repairs the PILGRED project
        $this->repairPilgredAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->kuanTi,
            target: $pilgredProject
        );
        $this->repairPilgredAction->execute();

        // then Chun's efficiency should be reset to 1-1%
        $I->assertEquals(new PlayerEfficiency(1, 1), $this->chun->getEfficiencyForProject($pilgredProject));
    }

    public function shouldCreateANeronAnnouncementWhenPilgredIsFinished(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->daedalus->getPilgred();

        // given PILGRED progress is 99%
        $pilgredProject->makeProgressAndUpdateParticipationDate(99);

        // when Chun repairs the PILGRED project
        $this->repairPilgredAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $pilgredProject
        );
        $this->repairPilgredAction->execute();

        // then a Neron announcement should be created
        $neronAnnouncement = $I->grabEntityFromRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::REPAIRED_PILGRED],
        );
        $I->assertEquals($neronAnnouncement->getTranslationParameters()['character'], 'chun');
    }

    public function shouldNotBeVisibleIfPilgredIsFinished(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->daedalus->getPilgred();

        // given PILGRED progress is 100%
        $pilgredProject->makeProgressAndUpdateParticipationDate(100);

        // when Chun tries to repair the PILGRED project
        $this->repairPilgredAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $pilgredProject
        );
        $this->repairPilgredAction->execute();

        // then the action should not be visible
        $I->assertFalse($this->repairPilgredAction->isVisible());
    }

    private function setPlayerProjectEfficiencyToZero(Player $player, Project $project): void
    {
        for ($i = 0; $i < $player->getEfficiencyForProject($project)->max; ++$i) {
            $project->addPlayerParticipation($player);
        }
    }
}
