<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Event;

use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Daedalus\Entity\DaedalusProjectsStatistics;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    public function shouldPutDaedalusShieldToFiftyPoints(FunctionalTester $I): void
    {
        // when Plasma Shield project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            author: $this->chun,
            I: $I
        );

        // then Daedalus has 50 points of Plasma Shield
        $I->assertEquals(50, $this->daedalus->getShield());
    }

    public function shouldBeAddedCorrectlyToDaedalusProjectsStatistics(FunctionalTester $I): void
    {
        // given it has a new DaedalusProjectsStatistics
        $this->daedalus->getDaedalusInfo()->setDaedalusProjectsStatistics(new DaedalusProjectsStatistics());

        // given a project of each category is finished
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::ANABOLICS),
            $this->chun,
            $I
        );
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::ARMOUR_CORRIDOR),
            $this->chun,
            $I
        );
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::PILGRED),
            $this->chun,
            $I
        );

        $daedalusProjectsStatistics = $this->daedalus->getDaedalusInfo()->getDaedalusProjectsStatistics();

        $I->assertTrue($daedalusProjectsStatistics->getResearchProjetsCompleted() === ['anabolics']);
        $I->assertTrue($daedalusProjectsStatistics->getNeronProjectsCompleted() === ['armour_corridor']);
        $I->assertTrue($daedalusProjectsStatistics->getPilgredProjetsCompleted() === ['pilgred']);
    }
}
