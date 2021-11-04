<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
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
        if (!$parameter instanceof GameEquipment) {
            throw new UnexpectedTypeException($parameter, GameEquipment::class);
        }

        /** @var Ration $rationMechanic */
        $rationMechanic = $parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::RATION);

        if (!$rationMechanic || !$rationMechanic->isPerishable()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
