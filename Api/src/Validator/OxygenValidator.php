<?php


namespace Mush\Action\Validator;


use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OxygenValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        $daedalus = $value->getPlayer()->getDaedalus();

        if ($constraint->retrieve && $daedalus->getOxygen() <= 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

        if (!$constraint->retrieve && $daedalus->getOxygen() >= $daedalus->getGameConfig()->getDaedalusConfig()->getMaxOxygen()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}