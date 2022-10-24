<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NumberPlayersInRoomValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof NumberPlayersInRoom) {
            throw new UnexpectedTypeException($constraint, NumberPlayersInRoom::class);
        }

        if ($value->getPlayer()->getPlace()->getNumberPlayers() !== $constraint->number) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
