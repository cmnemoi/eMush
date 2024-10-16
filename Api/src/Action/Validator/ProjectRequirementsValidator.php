<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Project\Entity\Project;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ProjectRequirementsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        $action = $value;

        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ProjectRequirements) {
            throw new UnexpectedTypeException($constraint, ProjectFinished::class);
        }

        $player = $action->getPlayer();
        $project = $action->getTarget();
        if (!$project instanceof Project) {
            throw new UnexpectedTypeException($project, Project::class);
        }

        if (!$project->isVisibleFor($player)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
