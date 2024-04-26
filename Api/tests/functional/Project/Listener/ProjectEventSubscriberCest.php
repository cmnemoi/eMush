<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Listener;

use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Listener\ProjectEventSubscriber;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectEventSubscriberCest extends AbstractFunctionalTest
{
    private ProjectEventSubscriber $projectEventSubscriber;
    private Project $projectToFinish;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->projectEventSubscriber = $I->grabService(ProjectEventSubscriber::class);

        // given I have 3 proposed NERON projects
        for ($i = 0; $i < 2; ++$i) {
            $project = $this->createProject(ProjectName::PATROL_SHIP_BLASTER_GUN, $I);
            $project->propose();
        }
        $this->projectToFinish = $this->createProject(ProjectName::PATROL_SHIP_BLASTER_GUN, $I);
        $this->projectToFinish->propose();
    }

    public function shouldUnproposeCurrentProjectsIfFinishedProjectIsANeronOne(FunctionalTester $I): void
    {
        // given I have a project event
        $projectEvent = new ProjectEvent(
            project: $this->projectToFinish,
            author: $this->chun,
        );

        // when I call onProjectFinished method
        $this->projectEventSubscriber->onProjectFinished($projectEvent);

        // then all NERON projects should be unproposed
        $I->assertCount(
            expectedCount: 0,
            haystack: $this->daedalus->getProposedNeronProjects(),
        );
    }

    public function shouldProposeNewProjectsIfFinishedProjectIsANeronOne(FunctionalTester $I): void
    {
        // given I have a project event
        $projectEvent = new ProjectEvent(
            project: $this->projectToFinish,
            author: $this->chun,
        );

        // given I have 1 unproposed NERON project in stock
        $plasmaShieldProject = $this->createProject(ProjectName::PLASMA_SHIELD, $I);

        // when I call onProjectFinished method
        $this->projectEventSubscriber->onProjectFinished($projectEvent);

        // then plasma shield should be proposed
        $I->assertTrue($plasmaShieldProject->isProposed());
    }

    public function shouldNotUnproposedProjectsIfFinishedProjectIsNotANeronOne(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgred = $this->createProject(ProjectName::PILGRED, $I);

        // given I have a project event
        $projectEvent = new ProjectEvent(
            project: $pilgred,
            author: $this->chun,
        );

        // when I call onProjectFinished method
        $this->projectEventSubscriber->onProjectFinished($projectEvent);

        // then all NERON projects should still be proposed
        $I->assertCount(
            expectedCount: 3,
            haystack: $this->daedalus->getProposedNeronProjects(),
        );
    }
}
