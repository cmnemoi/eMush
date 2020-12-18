<?php

namespace Mush\Communication\Validator;

use Mush\Communication\Entity\Message;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

class MaxNestedParentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$value instanceof Message) {
            throw new UnexpectedValueException($value, Message::class);
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
