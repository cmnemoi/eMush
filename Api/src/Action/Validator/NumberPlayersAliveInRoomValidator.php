<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NumberPlayersAliveInRoomValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof NumberPlayersAliveInRoom) {
            throw new UnexpectedTypeException($constraint, NumberPlayersAliveInRoom::class);
        }

        if ($value->getPlayer()->getPlace()->getNumberOfPlayersAlive() !== $constraint->number) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
