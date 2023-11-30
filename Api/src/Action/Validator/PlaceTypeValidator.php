<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PlaceTypeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof PlaceType) {
            throw new UnexpectedTypeException($constraint, PlaceType::class);
        }

        if (
            ($value->getPlayer()->getPlace()->getType() !== $constraint->type && $constraint->allowIfTypeMatches)
            || ($value->getPlayer()->getPlace()->getType() === $constraint->type && !$constraint->allowIfTypeMatches)
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
