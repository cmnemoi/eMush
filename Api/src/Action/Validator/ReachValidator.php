<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReachValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof Reach) {
            throw new UnexpectedTypeException($constraint, Reach::class);
        }

        $parameter = $value->getParameter();

        if ($parameter instanceof Player) {
            if ($parameter === $value->getPlayer() ||
                $parameter->getPlace() !== $value->getPlayer()->getPlace()
            ) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        } elseif ($parameter instanceof GameEquipment) {
            if (!$value->getPlayer()->canReachEquipment($parameter)) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
