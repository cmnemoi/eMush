<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Repository\ProjectRepositoryInterface;

final class CreateProjectFromConfigUseCase
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository
    ) {}

    public function execute(ProjectConfig $projectConfig): void
    {
        $this->projectRepository->save(new Project($projectConfig));
    }
}
