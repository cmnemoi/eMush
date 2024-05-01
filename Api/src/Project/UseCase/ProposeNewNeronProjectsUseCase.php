<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Repository\ProjectRepositoryInterface;

final readonly class ProposeNewNeronProjectsUseCase
{
    public function __construct(
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArrayService,
        private ProjectRepositoryInterface $projectRepository,
    ) {}

    public function execute(Daedalus $daedalus, int $number): void
    {
        $projectsToPropose = $this->getRandomElementsFromArrayService->execute(
            elements: $daedalus->getAvailableNeronProjects()->toArray(),
            number: $number
        );
        $projectsToPropose->map(static fn (Project $project) => $project->propose());
        $projectsToPropose->map(fn (Project $project) => $this->projectRepository->save($project));
    }
}
