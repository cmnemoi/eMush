<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ActionPointValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ActionPoint) {
            throw new UnexpectedTypeException($constraint, ActionPoint::class);
        }


        if ($value->getPlayer()->getActionPoint() < $value->getActionPointCost() ||
            $value->getPlayer()->getMoralPoint() < $value->getMoralPointCost()
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
