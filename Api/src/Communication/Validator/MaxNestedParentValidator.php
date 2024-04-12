<?php

namespace Mush\Communication\Validator;

use Mush\Communication\Entity\Message;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MaxNestedParentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof MaxNestedParent) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\MaxNestedParent');
        }

        if (!$value instanceof Message) {
            throw new \UnexpectedValueException($value);
        }

        if ($value->getParent()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(MaxNestedParent::MAXIMUM_NESTED_MESSAGE_ERROR)
                ->addViolation();
        }
    }
}
