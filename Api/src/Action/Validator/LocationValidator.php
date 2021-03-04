<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Enum\ReachEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LocationValidator extends ConstraintValidator
{
    //@FIXME: can it be part of ReachValidator?
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof Location) {
            throw new UnexpectedTypeException($constraint, Location::class);
        }

        switch ($constraint->location) {
            case ReachEnum::INVENTORY:
                if (!$value->getPlayer()->getItems()->contains($value->getParameter())) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                }
                break;
            case ReachEnum::SHELVE:
                if (!$value->getPlayer()->getPlace()->getEquipments()->contains($value->getParameter())) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                }
                break;
        }
    }
}
