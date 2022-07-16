<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Place\Enum\RoomEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * This class implements a validator for the `IsMedlabRoom` constraint.
 */
class IsMedlabRoomValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof IsMedlabRoom) {
            throw new UnexpectedTypeException($constraint, IsMedlabRoom::class);
        }

        $roomName = $value->getPlayer()->getPlace()->getName();
        $IsMedlabRoom = $roomName === RoomEnum::MEDLAB;

        if ($IsMedlabRoom !== $constraint->expectedValue) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
