<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Doctrine\ORM\NoResultException;
use Mush\Action\Actions\RunHome;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusService;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class RunHomeCest extends AbstractExplorationTester
{
    private ActionConfig $actionConfig;
    private RunHome $runHome;

    private Exploration $exploration;
    private StatusService $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::RUN_HOME]);
        $this->runHome = $I->grabService(RunHome::class);
        $this->statusService = $I->grabService(StatusService::class);

        $this->addSkillToPlayer(SkillEnum::U_TURN, $I);
    }

    public function shouldCloseExploration(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);

        $this->whenChunExecutesRunHomeAction();

        $this->thenExplorationShouldBeClosed($I);
    }

    public function shouldSendNotificationToAllExplorators(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);

        $this->whenChunExecutesRunHomeAction();

        $this->thenNotificationShouldBeSentToAllExplorators($I);
    }

    public function shouldNotBeVisibleIfPlayerIsNotExploring(FunctionalTester $I): void
    {
        $this->whenChunTriesToRunHome($I);

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfPlayerIsLost(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);
        $this->givenChunIsLost($I);

        $this->whenChunTriesToRunHome($I);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::RUN_HOME_LOST, $I);
    }

    private function givenPlayersAreInAnExpedition(FunctionalTester $I): void
    {
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet(
                sectors: [PlanetSectorEnum::OXYGEN],
                functionalTester: $I
            ),
            explorators: $this->players,
        );
    }

    private function givenChunIsLost(FunctionalTester $I): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenChunExecutesRunHomeAction(): void
    {
        $this->runHome->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun
        );
        $this->runHome->execute();
    }

    private function whenChunTriesToRunHome(FunctionalTester $I): void
    {
        $this->runHome->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun
        );
    }

    private function thenExplorationShouldBeClosed(FunctionalTester $I): void
    {
        $I->assertTrue($this->exploration->isFinished());
    }

    private function thenNotificationShouldBeSentToAllExplorators(FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            try {
                $notification = $I->grabEntityFromRepository(
                    entity: PlayerNotification::class,
                    params: [
                        'player' => $player,
                        'message' => PlayerNotificationEnum::EXPLORATION_CLOSED_BY_U_TURN->toString(),
                    ]
                );
            } catch (NoResultException $e) {
                $I->fail("Player {$player->getName()} has not received \"exploration closed by U turn\" notification.");
            }
            $I->assertTrue(isset($notification->getParameters()[$this->chun->getLogKey()]));
            $I->assertEquals($this->chun->getLogName(), $notification->getParameters()[$this->chun->getLogKey()]);
        }
        $I->dontSeeInRepository(
            entity: PlayerNotification::class,
            params: [
                'message' => PlayerNotificationEnum::EXPLORATION_CLOSED->toString(),
            ]
        );
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->runHome->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->runHome->cannotExecuteReason());
    }
}
