<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Item;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PlantWaterableValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof PlantWaterable) {
            throw new UnexpectedTypeException($constraint, PlantWaterable::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof Item) {
            throw new UnexpectedTypeException($parameter, Equipment::class);
        }

        if ($parameter->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY) === null &&
            $parameter->getStatusByName(EquipmentStatusEnum::PLANT_DRY) === null
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
