<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\ConstraintValidator;

final class ExplorationAlreadyOngoingValidator extends ConstraintValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }
        if (!$constraint instanceof ExplorationAlreadyOngoing) {
            throw new UnexpectedTypeException($constraint, ExplorationAlreadyOngoing::class);
        }

        $onGoingExploration = $value->getPlayer()->getDaedalus()->getExploration();

        if ($onGoingExploration !== null) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
