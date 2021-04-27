<?php


namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FuelValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Fuel) {
            throw new UnexpectedTypeException($constraint, Fuel::class);
        }

        /** @var GameEquipment $param */
        $daedalus = $value->getPlayer()->getDaedalus();

        if ($constraint->retrieve && $daedalus->getFuel() <= 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }

        if (!$constraint->retrieve && $daedalus->getFuel() >= $daedalus->getGameConfig()->getDaedalusConfig()->getMaxFuel()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
