<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
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

        $actionSupport = $value->getSupport();
        if (!$actionSupport instanceof GameEquipment) {
            throw new UnexpectedTypeException($actionSupport, GameEquipment::class);
        }

        if (!$actionSupport->isBreakable()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
