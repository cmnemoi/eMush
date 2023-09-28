<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MechanicValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Mechanic) {
            throw new UnexpectedTypeException($constraint, Mechanic::class);
        }

        $actionSupport = $value->getSupport();
        if (!$actionSupport instanceof GameEquipment) {
            throw new UnexpectedTypeException($actionSupport, GameEquipment::class);
        }

        if ($actionSupport->getEquipment()->getMechanicByName($constraint->mechanic) === null) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
