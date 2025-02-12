<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NoMoreNeronProjectsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $action = $value;

        if (!$action instanceof AbstractAction) {
            throw new UnexpectedTypeException($action, AbstractAction::class);
        }

        if (!$constraint instanceof NoMoreNeronProjects) {
            throw new UnexpectedTypeException($constraint, NoMoreNeronProjects::class);
        }

        $daedalus = $action->getPlayer()->getDaedalus();

        if ($daedalus->getAvailableNeronProjects()->isEmpty()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
