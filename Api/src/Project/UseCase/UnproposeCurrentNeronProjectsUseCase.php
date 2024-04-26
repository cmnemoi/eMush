<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Project\Entity\Project;
use Mush\Project\Repository\ProjectRepositoryInterface;

final readonly class UnproposeCurrentNeronProjectsUseCase
{
    public function __construct(private ProjectRepositoryInterface $projectRepository) {}

    public function execute(Daedalus $daedalus): void
    {
        $proposedProjects = $daedalus->getProposedNeronProjects();

        $proposedProjects->map(static fn (Project $project) => $project->unpropose());
        $proposedProjects->map(fn (Project $project) => $this->projectRepository->save($project));
    }
}
