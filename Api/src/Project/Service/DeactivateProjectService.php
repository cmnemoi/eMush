<?php

declare(strict_types=1);

namespace Mush\Project\Service;

use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Repository\ProjectRepositoryInterface;

final readonly class DeactivateProjectService
{
    public function __construct(
        private ModifierCreationServiceInterface $modifierCreationService,
        private ProjectRepositoryInterface $projectRepository
    ) {}

    public function execute(Project $project): void
    {
        $project->deactivate();
        $this->projectRepository->save($project);

        foreach ($project->getAllModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $project->getDaedalus(),
                modifierProvider: $project
            );
        }
    }
}
