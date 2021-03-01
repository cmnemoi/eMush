<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Place\Enum\PlaceTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RoomValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof Room) {
            throw new UnexpectedTypeException($constraint, Room::class);
        }

        if ($value->getPlayer()->getPlace()->getType() === PlaceTypeEnum::ROOM) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
