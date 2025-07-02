<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Event;

use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldIncreasePlayerMaxActionPointsWhenNoiseReducerIsFinished(FunctionalTester $I): void
    {
        $this->givenPlayerHasMaxActionPoints(12, $I);

        $this->whenNoiseReducerProjectIsFinished($I);

        $this->thenPlayerMaxActionPointsShouldBe(14, $I);
    }

    public function shouldCreatePublicLogAndHighlightWhenResearchIsFinished(FunctionalTester $I): void
    {
        // given research project is finished
        $research = $this->daedalus->getProjectByName(ProjectName::RETRO_FUNGAL_SERUM);
        $research->finish();

        // given I have a project finished event for a research project
        $projectEvent = new ProjectEvent(
            project: $research,
            author: $this->chun,
        );

        // when I dispatch the event
        $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_FINISHED);

        // then highlight about research finished should be created
        $I->assertEquals(
            expected: [
                'name' => 'project.finished_research',
                'result' => PlayerHighlight::SUCCESS,
                'parameters' => ['target_project' => $research->getLogName()],
            ],
            actual: $this->chun->getPlayerInfo()->getPlayerHighlights()[0]->toArray(),
        );
    }

    public function shouldNotCreateMessageOrHighlightWhenNeronProjectIsFinished(FunctionalTester $I): void
    {
        // given neron project is finished
        $neronProject = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $neronProject->finish();

        // when I dispatch the event
        $this->eventService->callEvent(
            event: new ProjectEvent(
                project: $neronProject,
                author: $this->chun,
            ),
            name: ProjectEvent::PROJECT_FINISHED,
        );

        // then highlight about research finished not should be created
        $I->assertNotEquals(
            expected: [
                'name' => 'project.finished_research',
                'result' => PlayerHighlight::SUCCESS,
                'parameters' => ['target_project' => $neronProject->getLogName()],
            ],
            actual: $this->chun->getPlayerInfo()->getPlayerHighlights()[0]->toArray(),
        );
    }

    private function givenPlayerHasMaxActionPoints(int $maxActionPoints, FunctionalTester $I): void
    {
        $this->player->getVariableByName(PlayerVariableEnum::ACTION_POINT)->setMaxValue($maxActionPoints);
    }

    private function whenNoiseReducerProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::NOISE_REDUCER),
            author: $this->player,
            I: $I
        );
    }

    private function thenPlayerMaxActionPointsShouldBe(int $maxActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $maxActionPoints,
            actual: $this->player->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValue()
        );
    }
}
