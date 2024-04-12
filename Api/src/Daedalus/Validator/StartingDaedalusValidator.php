<?php

namespace Mush\Daedalus\Validator;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StartingDaedalusValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Daedalus) {
            throw new \InvalidArgumentException();
        }

        if (!$constraint instanceof StartingDaedalus) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\StartingDaedalus');
        }

        if (!\in_array($value->getGameStatus(), [GameStatusEnum::STARTING, GameStatusEnum::STANDBY], true)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(StartingDaedalus::STARTING_DAEDALUS_ERROR)
                ->addViolation();
        }
    }
}
