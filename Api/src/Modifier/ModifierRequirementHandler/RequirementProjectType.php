<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Project\Entity\Project;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;

final class RequirementProjectType extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::PROJECT_TYPE;

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        if (!$holder instanceof Project) {
            throw new UnexpectedTypeException($holder, Project::class);
        }

        return $modifierRequirement->getActivationRequirement() === $holder->getType()->value;
    }
}
