<?php

namespace Mush\Communication\Validator;

use Mush\Communication\Entity\Message;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use UnexpectedValueException;

class MaxNestedParentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof NotNull) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\NotNull');
        }

        if (!$value instanceof Message) {
            throw new UnexpectedValueException($value);
        }

        if ($value->getParent()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(MaxNestedParent::MAXIMUM_NESTED_MESSAGE_ERROR)
                ->addViolation()
            ;
        }
    }
}
