<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Disease\Enum\DiseaseEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * This class implements a validator for the `HasAllFakeDiseases` constraint.
 */
class HasAllFakeDiseasesValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasAllFakeDiseases) {
            throw new UnexpectedTypeException($constraint, HasAllFakeDiseases::class);
        }

        $fakeDiseases = DiseaseEnum::getFakeDiseases();
        $playerDiseases = $value->getPlayer()->getMedicalConditions()->toArray();

        if (count($fakeDiseases) === count($playerDiseases)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
