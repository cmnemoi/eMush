<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class MaxLengthConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof MaxLengthConstraint) {
            throw new UnexpectedTypeException($constraint, MaxLengthConstraint::class);
        }

        $params = $value->getParameters();
        $parameterName = $constraint->parameterName;
        if (!$params || !\array_key_exists($parameterName, $params)) {
            return;
        }

        $text = $params[$parameterName];
        if (\is_string($text) && mb_strlen($text) > $constraint->maxLength) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
