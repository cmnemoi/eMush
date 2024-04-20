<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Project\Entity\Project;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ProjectFinishedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        $action = $value;

        $project = $action->getTarget();
        if (!$project instanceof Project) {
            throw new UnexpectedTypeException($project, Project::class);
        }

        if (!$constraint instanceof ProjectFinished) {
            throw new UnexpectedTypeException($constraint, ProjectFinished::class);
        }

        if ($project->isFinished()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
