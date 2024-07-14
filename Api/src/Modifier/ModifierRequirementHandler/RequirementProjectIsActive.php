<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Project\Enum\ProjectName;

final class RequirementProjectIsActive extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::PROJECT_IS_ACTIVE;

    public function checkRequirement(ModifierActivationRequirement $modifierRequirement, ModifierHolderInterface $holder): bool
    {
        $daedalus = $holder->getDaedalus();

        $projectName = $modifierRequirement->getActivationRequirement();
        if ($projectName === null) {
            throw new \InvalidArgumentException("{$this->name} activation requirement value is missing.");
        }

        return $daedalus->hasActiveProject(ProjectName::from($projectName));
    }
}
