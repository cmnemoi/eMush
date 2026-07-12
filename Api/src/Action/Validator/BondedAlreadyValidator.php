<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BondedAlreadyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof BondedAlready) {
            throw new UnexpectedTypeException($constraint, BondedAlready::class);
        }

        $initiator = $constraint->initiator ? $value->getPlayer() : $value->getTarget();
        $target = $constraint->initiator ? $value->getTarget() : $value->getPlayer();

        if (!$initiator instanceof Player) {
            throw new UnexpectedTypeException($initiator, Player::class);
        }

        if (!$target instanceof Player) {
            throw new UnexpectedTypeException($target, Player::class);
        }

        if ($initiator->hasBondeddWith($target) !== $constraint->expectedValue) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
