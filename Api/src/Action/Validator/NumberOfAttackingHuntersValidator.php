<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NumberOfAttackingHuntersValidator extends ConstraintValidator
{
    public const LESS_THAN = 'less_than';
    public const GREATER_THAN = 'greater_than';
    public const EQUAL = 'equal';

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof NumberOfAttackingHunters) {
            throw new UnexpectedTypeException($constraint, NumberOfAttackingHunters::class);
        }

        $nbAttackingHunters = $value->getPlayer()->getDaedalus()->getAttackingHunters()->count();

        $buildViolation = false;

        switch ($constraint->mode) {
            case self::GREATER_THAN:
                $buildViolation = $nbAttackingHunters > $constraint->number;

                break;

            case self::LESS_THAN:
                $buildViolation = $nbAttackingHunters < $constraint->number;

                break;

            case self::EQUAL:
                $buildViolation = $nbAttackingHunters === $constraint->number;

                break;
        }

        if ($buildViolation) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
