<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Game\Service\GetRandomIntegerServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Repository\ProjectRepositoryInterface;

final class AdvanceProjectUseCase 
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepositoryInterface,
        private GetRandomIntegerServiceInterface $getRandomIntegerService,
    ){}

    public function execute(Project $project): void
    {   
        $delta = $this->getRandomIntegerService->execute($project->getMinEfficiency(), $project->getMaxEfficiency());
        $project->makeProgress($delta);

        $this->projectRepositoryInterface->save($project);
    }

}