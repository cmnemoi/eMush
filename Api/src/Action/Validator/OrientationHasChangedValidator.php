<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OrientationHasChangedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof OrientationHasChanged) {
            throw new UnexpectedTypeException($constraint, OrientationHasChanged::class);
        }

        $parameters = $value->getParameters();

        if ($parameters === null) {
            throw new \InvalidArgumentException('Parameters should not be null');
        }

        $chosenOrientation = $parameters['orientation'];
        $currentOrientation = $value->getPlayer()->getDaedalus()->getOrientation();

        if ($chosenOrientation === $currentOrientation) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
