<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Project\Entity\Project;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NoEfficiencyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        $action = $value;

        if (!$action instanceof AbstractAction) {
            throw new UnexpectedTypeException($action, AbstractAction::class);
        }

        if (!$constraint instanceof NoEfficiency) {
            throw new UnexpectedTypeException($constraint, NoEfficiency::class);
        }

        $player = $action->getPlayer();
        $project = $action->getTarget();
        if (!$project instanceof Project) {
            throw new UnexpectedTypeException($project, Project::class);
        }

        if ($player->efficiencyIsZeroForProject($project)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
