<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ParameterNameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ParameterName) {
            throw new UnexpectedTypeException($constraint, ParameterName::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof GameEquipment) {
            throw new UnexpectedTypeException($parameter, GameEquipment::class);
        }

        if ($parameter->getEquipment()->getName() !== $constraint->name) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
