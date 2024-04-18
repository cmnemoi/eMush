<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PlayerAliveValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof PlayerAlive) {
            throw new UnexpectedTypeException($constraint, Reach::class);
        }

        $player = $value->getPlayer();

        if (!$player->isAlive()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
