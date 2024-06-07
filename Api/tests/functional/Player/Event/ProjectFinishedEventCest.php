<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Event;

use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    public function shouldIncreasePlayerMaxActionPointsWhenNoiseReducerIsFinished(FunctionalTester $I): void
    {
        $this->givenPlayerHasMaxActionPoints(12, $I);

        $this->whenNoiseReducerProjectIsFinished($I);

        $this->thenPlayerMaxActionPointsShouldBe(14, $I);
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
