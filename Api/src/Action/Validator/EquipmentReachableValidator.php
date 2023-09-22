<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Service\GearToolServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EquipmentReachableValidator extends ConstraintValidator
{
    private GearToolServiceInterface $gearToolService;

    public function __construct(GearToolServiceInterface $gearToolService)
    {
        $this->gearToolService = $gearToolService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof EquipmentReachable) {
            throw new UnexpectedTypeException($constraint, EquipmentReachable::class);
        }

        if ($this->gearToolService->getEquipmentsOnReachByName($value->getPlayer(), $constraint->name)->isEmpty()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
