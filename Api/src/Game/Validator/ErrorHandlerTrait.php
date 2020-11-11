<?php

namespace Mush\Game\Validator;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ErrorHandlerTrait
{
    public function handleErrors(ConstraintViolationListInterface $violationList)
    {
        $errors = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violationList as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errors;
    }
}
