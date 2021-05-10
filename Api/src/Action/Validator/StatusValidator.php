<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Status) {
            throw new UnexpectedTypeException($constraint, Status::class);
        }

        $target = match ($constraint->target) {
            Status::PARAMETER => $value->getParameter(),
            Status::PLAYER => $value->getPlayer(),
            Status::PLAYER_ROOM => $value->getPlayer()->getPlace(),
            default => throw new LogicException('unsupported target'),
        };

        if (!$target instanceof StatusHolderInterface) {
            throw new UnexpectedTypeException($target, StatusHolderInterface::class);
        }

        if ($constraint->ownerSide && $target->hasStatus($constraint->status) !== $constraint->contain) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
        if (!$constraint->ownerSide && $target->hasTargetingStatus($constraint->status) !== $constraint->contain) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
