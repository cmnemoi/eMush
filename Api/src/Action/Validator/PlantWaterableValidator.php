<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
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

        $actionSupport = $value->getSupport();
        if (!$actionSupport instanceof GameItem) {
            throw new UnexpectedTypeException($actionSupport, GameEquipment::class);
        }

        if ($actionSupport->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY) === null
            && $actionSupport->getStatusByName(EquipmentStatusEnum::PLANT_DRY) === null
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
