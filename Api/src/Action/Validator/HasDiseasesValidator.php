<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * This class implements a validator for the `HasDiseases` constraint.
 */
class HasDiseasesValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasDiseases) {
            throw new UnexpectedTypeException($constraint, HasDiseases::class);
        }

        /** @var Player $target */
        $target = match ($constraint->target) {
            HasDiseases::PARAMETER => $value->getTarget(),
            HasDiseases::PLAYER => $value->getPlayer(),
            default => throw new LogicException('unsupported target'),
        };

        $playerDiseases = $target->getMedicalConditions();

        $type = $constraint->type;
        $isEmpty = $constraint->isEmpty;

        if ($type !== null) {
            $playerDiseases = $playerDiseases->getByDiseaseType($type);
        }
        if (($playerDiseases->count() === 0) !== $isEmpty) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
