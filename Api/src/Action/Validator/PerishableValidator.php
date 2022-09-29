<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PerishableValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Perishable) {
            throw new UnexpectedTypeException($constraint, Perishable::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof Equipment) {
            throw new UnexpectedTypeException($parameter, Equipment::class);
        }

        /** @var Ration $rationMechanic */
        $rationMechanic = $parameter->getConfig()->getMechanicByName(EquipmentMechanicEnum::RATION);

        if (!$rationMechanic || !$rationMechanic->isPerishable()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
