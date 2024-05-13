<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Project\Entity\Project;
use Mush\Project\Repository\ProjectRepositoryInterface;

final class MakeNonFinishedNeronProjectsAvailableUseCase
{
    public function __construct(private ProjectRepositoryInterface $projectRepository) {}

    public function execute(Daedalus $daedalus): void
    {
        $projects = $daedalus->getNonFinishedNeronProjects();

        $projects->map(static fn (Project $project) => $project->makeAvailable());
        $projects->map(fn (Project $project) => $this->projectRepository->save($project));
    }
}
