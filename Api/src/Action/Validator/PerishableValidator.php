<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\Mechanics\Ration;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PerishableValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        /** @var Ration $rationMechanic */
        $rationMechanic = $value->getParameter()->getEquipment()->getRationsMechanic();

        if (!$rationMechanic->isPerishable()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
