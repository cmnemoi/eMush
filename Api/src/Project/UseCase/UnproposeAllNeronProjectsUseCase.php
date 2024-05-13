<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Project\Entity\Project;
use Mush\Project\Repository\ProjectRepositoryInterface;

final class UnproposeAllNeronProjectsUseCase
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
    ) {}

    public function execute(Daedalus $daedalus): void
    {
        $projectsToUnpropose = $daedalus->getProposedNeronProjects();

        $projectsToUnpropose->map(static fn (Project $project) => $project->unpropose());
        $projectsToUnpropose->map(fn (Project $project) => $this->projectRepository->save($project));
    }
}
