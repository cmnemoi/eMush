<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EquipmentInfectedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof EquipmentInfected) {
            throw new UnexpectedTypeException($constraint, EquipmentInfected::class);
        }

        $equipment = $value->gameEquipmentTarget();

        foreach (EquipmentStatusEnum::getPetInfectedStatus() as $itemName => $statusName) {
            if ($equipment->hasStatus($statusName)) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
