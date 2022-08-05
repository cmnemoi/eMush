<?php

namespace Mush\Game\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;

class GroupValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $context = $this->context;

        if (!$constraint instanceof InputConstraintInterface) {
            throw new \LogicException('Validator is only intended for input validation');
        }

        $validator = $context->getValidator();
        $validations = $validator->validate($value, $constraint->getConstraints());

        if ($validations->count() > 0) {
            /** @var ConstraintViolationInterface $validation */
            foreach ($validations as $validation) {
                $this->context->buildViolation($validation->getMessage())
                    ->atPath(trim($validation->getPropertyPath(), '[]'))
                    ->addViolation();
            }
        }
    }
}
