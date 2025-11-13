<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\SpaceShip;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasBrokenPatrolShipValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasBrokenPatrolShip) {
            throw new UnexpectedTypeException($constraint, HasBrokenPatrolShip::class);
        }

        $target = $value->getTarget();

        if (!$target instanceof SpaceShip) {
            return;
        }

        if ($target->isBroken()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
