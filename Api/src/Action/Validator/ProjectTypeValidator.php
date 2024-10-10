<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectType as ProjectTypeEnum;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ProjectTypeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /** @var AbstractAction $action */
        $action = $value;

        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ProjectType) {
            throw new UnexpectedTypeException($constraint, ProjectType::class);
        }

        /** @var Project $project */
        $project = $action->getTarget();

        if (!$this->isAnyTypeMatched($project->getType(), $constraint->types)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function isAnyTypeMatched(ProjectTypeEnum $type, array $types): bool
    {
        return \in_array($type, $types, true);
    }
}
