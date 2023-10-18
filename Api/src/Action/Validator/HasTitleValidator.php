<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasTitleValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasTitle) {
            throw new UnexpectedTypeException($constraint, HasTitle::class);
        }

        $actionTarget = $value->getTarget();

        if ($actionTarget instanceof GameEquipment) {
            if ($actionTarget->getName() === $constraint->terminal) {
                if (!$value->getPlayer()->hasTitle($constraint->title)) {
                    $this->context->buildViolation($constraint->message)->addViolation();
                }
            }
        }
    }
}
