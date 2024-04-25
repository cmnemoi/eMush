<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
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

        if (!$constraint instanceof ProjectFinished) {
            throw new UnexpectedTypeException($constraint, ProjectFinished::class);
        }

        $action = $value;
        $daedalus = $action->getPlayer()->getDaedalus();
        $mode = $constraint->mode;
        $projectName = $constraint->project;

        $project = $daedalus->getProjectByName($projectName);

        $allowAndProjectNotFinished = $mode === 'allow' && !$project->isFinished();
        $preventAndProjectFinished = $mode === 'prevent' && $project->isFinished();

        if ($allowAndProjectNotFinished || $preventAndProjectFinished) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
