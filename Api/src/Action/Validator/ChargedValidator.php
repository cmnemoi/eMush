<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ChargedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Charged) {
            throw new UnexpectedTypeException($constraint, Charged::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof GameEquipment) {
            throw new UnexpectedTypeException($parameter, GameEquipment::class);
        }

        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $parameter->getStatusByName(EquipmentStatusEnum::CHARGES);

        if (!$chargeStatus || $chargeStatus->getCharge() <= 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
