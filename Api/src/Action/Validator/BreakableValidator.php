<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\Equipment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BreakableValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Breakable) {
            throw new UnexpectedTypeException($constraint, Breakable::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof Equipment) {
            throw new UnexpectedTypeException($parameter, Equipment::class);
        }

        if (!$parameter->isBreakable()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
