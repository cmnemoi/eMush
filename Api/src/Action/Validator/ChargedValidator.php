<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ChargedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof GameEquipment) {
            throw new UnexpectedTypeException($parameter, GameEquipment::class);
        }

        $chargeStatus = $parameter->getStatusByName(EquipmentStatusEnum::CHARGES);

        if (!$chargeStatus || $chargeStatus->getCharge() <= 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
