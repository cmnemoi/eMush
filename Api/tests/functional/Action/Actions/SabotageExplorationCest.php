<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\SabotageExploration;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusService;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class SabotageExplorationCest extends AbstractExplorationTester
{
    private ActionConfig $actionConfig;
    private SabotageExploration $sabotageExploration;
    private Exploration $exploration;
    private StatusService $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SABOTAGE_EXPLORATION]);
        $this->sabotageExploration = $I->grabService(SabotageExploration::class);
        $this->statusService = $I->grabService(StatusService::class);

        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->addSkillToPlayer(SkillEnum::TRAITOR, $I, $this->kuanTi);
    }

    public function shouldSabotageExploration(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);

        $this->whenKuanTiExecutesSabotageExplorationAction();

        $this->thenExplorationShouldBeSabotaged($I);
    }

    public function shouldCreatePrivateLog(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);

        $this->whenKuanTiExecutesSabotageExplorationAction();

        $I->canSeeInRepository(
            entity: RoomLog::class,
            params: [
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => 'sabotage_exploration_success',
                'playerInfo' => $this->kuanTi->getPlayerInfo(),
            ]
        );
    }

    public function shouldNotBeVisibleIfPlayerIsNotExploring(FunctionalTester $I): void
    {
        $this->whenKuanTiTriesToSabotageExploration($I);

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfPlayerIsLost(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);
        $this->givenKuanTiIsLost($I);

        $this->whenKuanTiTriesToSabotageExploration($I);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::SABOTAGE_EXPLORATION_LOST, $I);
    }

    public function shouldNotBeExecutableIfExplorationIsSabotaged(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);
        $this->givenExplorationIsAlreadySabotaged($I);

        $this->whenKuanTiTriesToSabotageExploration($I);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::EXPLORATION_ALREADY_SABOTAGED, $I);
    }

    public function shouldNotBeExecutableTwicePerExpedition(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);

        $this->whenKuanTiExecutesSabotageExplorationAction();

        $this->whenKuanTiTriesToSabotageExploration($I);

        $this->thenKuanTiShouldHaveStatusHasUsedTraitor($I);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::SABOTAGE_EXPLORATION_SPENT, $I);
    }

    public function shouldIncrementTraitorUsedStatistic(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);

        $this->whenKuanTiExecutesSabotageExplorationAction();

        $I->assertEquals(1, $this->kuanTi->getPlayerInfo()->getStatistics()->getTraitorUsed());
    }

    public function shouldNotChangeUpdatedAt(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);

        $this->explorationService->persist([$this->exploration]);

        $updatedAt = $this->exploration->getLastVisitAtOrThrow()->getMicrosecond();

        $this->whenKuanTiExecutesSabotageExplorationAction();

        $I->assertEquals($updatedAt, $this->exploration->getLastVisitAtOrThrow()->getMicrosecond());
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

    private function givenKuanTiIsLost(FunctionalTester $I): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenExplorationIsAlreadySabotaged(FunctionalTester $I): void
    {
        $this->exploration->setIsSabotaged(true);
    }

    private function whenKuanTiExecutesSabotageExplorationAction(): void
    {
        $this->sabotageExploration->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi
        );
        $this->sabotageExploration->execute();
    }

    private function whenKuanTiTriesToSabotageExploration(FunctionalTester $I): void
    {
        $this->sabotageExploration->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi
        );
    }

    private function thenExplorationShouldBeSabotaged(FunctionalTester $I): void
    {
        $I->assertTrue($this->exploration->isSabotaged());
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->sabotageExploration->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->sabotageExploration->cannotExecuteReason());
    }

    private function thenKuanTiShouldHaveStatusHasUsedTraitor(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::HAS_USED_TRAITOR_THIS_EXPEDITION));
    }
}
