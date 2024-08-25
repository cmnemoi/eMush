<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class AllMushsAreDeadValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AllMushsAreDead) {
            throw new UnexpectedTypeException($constraint, AllMushsAreDead::class);
        }

        $daedalus = $value->getPlayer()->getDaedalus();

        if ($daedalus->getAlivePlayers()->getMushPlayer()->isEmpty()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}