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

        $actionSupport = $value->getSupport();
        if (!$actionSupport instanceof GameEquipment) {
            throw new UnexpectedTypeException($actionSupport, GameEquipment::class);
        }

        /** @var Ration $rationMechanic */
        $rationMechanic = $actionSupport->getEquipment()->getMechanicByName(EquipmentMechanicEnum::RATION);

        if (!$rationMechanic || !$rationMechanic->getIsPerishable()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
