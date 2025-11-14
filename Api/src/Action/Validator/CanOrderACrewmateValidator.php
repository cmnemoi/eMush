<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Chat\Services\GetAvailableSubordinatesForMissionService;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CanOrderACrewmateValidator extends ConstraintValidator
{
    public function __construct(private readonly GetAvailableSubordinatesForMissionService $getAvailaibleSubordinatesForMission) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof CanOrderACrewmate) {
            throw new UnexpectedTypeException($constraint, CanOrderACrewmate::class);
        }

        if ($this->getAvailaibleSubordinatesForMission->execute($value->getPlayer())->isEmpty()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
