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
    private array $currentlyProposedProjects;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->projectEventSubscriber = $I->grabService(ProjectEventSubscriber::class);

        // given I have 3 proposed NERON projects
        $this->currentlyProposedProjects = [];
        $this->currentlyProposedProjects[] = $this->daedalus->getProjectByName(ProjectName::AUTO_WATERING);
        $this->currentlyProposedProjects[] = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $this->currentlyProposedProjects[] = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $this->projectToFinish = $this->currentlyProposedProjects[0];
        foreach ($this->currentlyProposedProjects as $project) {
            $project->propose();
        }
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

        // then all currently NERON projects should be unproposed
        foreach ($this->currentlyProposedProjects as $project) {
            $I->assertFalse($project->isProposed());
        }
    }

    public function shouldProposeNewProjectsIfFinishedProjectIsANeronOne(FunctionalTester $I): void
    {
        // given I have a project event
        $projectEvent = new ProjectEvent(
            project: $this->projectToFinish,
            author: $this->chun,
        );

        // when I call onProjectFinished method
        $this->projectEventSubscriber->onProjectFinished($projectEvent);

        $newProjects = $this->daedalus->getProposedNeronProjects();

        // then new projects should be proposed
        $I->assertCount(
            expectedCount: $this->daedalus->getNumberOfProjectsByBatch(),
            haystack: $newProjects,
        );

        // then new proposed projects are different from the previous ones
        $I->assertNotEquals(
            $this->daedalus->getProjectByName(ProjectName::AUTO_WATERING),
            $newProjects[0],
        );
    }

    public function shouldNotUnproposedProjectsIfFinishedProjectIsNotANeronOne(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgred = $this->daedalus->getProjectByName(ProjectName::PILGRED);

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
