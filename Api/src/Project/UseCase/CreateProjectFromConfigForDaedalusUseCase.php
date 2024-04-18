<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Repository\ProjectRepositoryInterface;

final class CreateProjectFromConfigForDaedalusUseCase
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private ProjectRepositoryInterface $projectRepository
    ) {}

    public function execute(ProjectConfig $projectConfig, Daedalus $daedalus): void
    {
        $project = new Project($projectConfig);
        $this->projectRepository->save($project);

        $daedalus->addProject($project);
        $this->daedalusRepository->save($daedalus);
    }
}
