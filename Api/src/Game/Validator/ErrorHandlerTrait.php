<?php

namespace Mush\Game\Validator;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ErrorHandlerTrait
{
    /**
     * @return (string|\Stringable)[]
     *
     * @psalm-return array<string, \Stringable|string>
     */
    public function handleErrors(ConstraintViolationListInterface $violationList): array
    {
        $errors = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violationList as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errors;
    }
}
