<?php

declare(strict_types=1);

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Project\Entity\Project;

final class ProjectModifierService
{
    public function __construct(private ModifierCreationServiceInterface $modifierCreationService) {}

    public function createProjectModifiers(Project $project): void
    {
        /** @var AbstractModifierConfig $modifierConfig */
        foreach ($project->getAllModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $project->getDaedalus(),
                modifierProvider: $project,
                tags: [],
                time: new \DateTime()
            );
        }
    }

    public function deleteProjectModifiers(Project $project): void
    {
        /** @var AbstractModifierConfig $modifierConfig */
        foreach ($project->getAllModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $project->getDaedalus(),
                modifierProvider: $project,
                tags: [],
                time: new \DateTime()
            );
        }
    }
}
