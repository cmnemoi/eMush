<?php

declare(strict_types=1);

namespace Mush\Project\UseCase;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Repository\ProjectRepositoryInterface;

final readonly class CreateProjectFromConfigForDaedalusUseCase
{
    public function __construct(private ProjectRepositoryInterface $projectRepository) {}

    public function execute(ProjectConfig $projectConfig, Daedalus $daedalus): void
    {
        if ($daedalus->hasProject($projectConfig->getName())) {
            return;
        }

        $this->projectRepository->save(new Project($projectConfig, $daedalus));
    }
}
