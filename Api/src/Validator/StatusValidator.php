<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof Status) {
            throw new UnexpectedTypeException($constraint, Status::class);
        }

        switch ($constraint->target) {
            case Status::PARAMETER:
                $target = $value->getParameter();
                break;
            case Status::PLAYER:
                $target = $value->getPlayer();
                break;
            case Status::PLAYER_ROOM:
                $target = $value->getPlayer()->getPlace();
                break;
            default:
                throw new \LogicException('unsupported target');
        }

        if ($target->hasStatus($constraint->status) !== $constraint->contain) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}