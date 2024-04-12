<?php

namespace Mush\Daedalus\Validator;

use Mush\Daedalus\Entity\Daedalus;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FullDaedalusValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Daedalus) {
            throw new \InvalidArgumentException();
        }

        if (!$constraint instanceof FullDaedalus) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\FullDaedalus');
        }

        if ($value->getPlayers()->count() >= $value->getGameConfig()->getMaxPlayer()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(FullDaedalus::FULL_DAEDALUS_ERROR)
                ->addViolation();
        }
    }
}
