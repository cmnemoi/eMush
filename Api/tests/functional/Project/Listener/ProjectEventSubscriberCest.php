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
        $this->currentlyProposedProjects[] = $this->daedalus->getProjectByName(ProjectName::HEAT_LAMP);
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
            tags: [ProjectEvent::PROJECT_ADVANCED],
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
            tags: [ProjectEvent::PROJECT_ADVANCED],
        );

        // when I call onProjectFinished method
        $this->projectEventSubscriber->onProjectFinished($projectEvent);

        $newProjects = $this->daedalus->getProposedNeronProjects();

        // then old projects should not be proposed
        foreach ($this->currentlyProposedProjects as $project) {
            $I->assertFalse($project->isProposed(), 'Project ' . $project->getName() . ' should not be proposed');
        }

        // then new projects should be proposed and different from old ones
        $oldProjectsSet = $this->createProjectNamesSet($this->currentlyProposedProjects);
        foreach ($newProjects as $project) {
            $I->assertFalse(
                isset($oldProjectsSet[$project->getName()]),
                'Project ' . $project->getName() . ' should not be among previously proposed projects'
            );
        }
    }

    public function shouldNotUnproposedProjectsIfFinishedProjectIsNotANeronOne(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgred = $this->daedalus->getProjectByName(ProjectName::PILGRED);

        // given I have a project event
        $projectEvent = new ProjectEvent(
            project: $pilgred,
            author: $this->chun,
            tags: [ProjectEvent::PROJECT_ADVANCED],
        );

        // when I call onProjectFinished method
        $this->projectEventSubscriber->onProjectFinished($projectEvent);

        // then all NERON projects should still be proposed
        foreach ($this->currentlyProposedProjects as $project) {
            $I->assertTrue($project->isProposed(), 'Project ' . $project->getName() . ' should still be proposed');
        }
    }

    public function shouldNotUnproposeProjectsIfFinishedProjectWasNotAdvanced(FunctionalTester $I): void
    {
        // given I have a project event
        $projectEvent = new ProjectEvent(
            project: $this->projectToFinish,
            author: $this->chun,
            tags: [],
        );

        // when I call onProjectFinished method
        $this->projectEventSubscriber->onProjectFinished($projectEvent);

        // then all NERON projects should still be proposed
        foreach ($this->currentlyProposedProjects as $project) {
            $I->assertTrue($project->isProposed(), 'Project ' . $project->getName() . ' should still be proposed');
        }
    }

    private function createProjectNamesSet(array $projects): array
    {
        return array_flip(array_map(
            static fn (Project $project) => $project->getName(),
            $projects
        ));
    }
}
