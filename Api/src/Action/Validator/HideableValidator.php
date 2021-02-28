<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HideableValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof GameItem) {
            throw new UnexpectedTypeException($parameter, GameEquipment::class);
        }

        if (!$parameter->getEquipment()->isHideable()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
