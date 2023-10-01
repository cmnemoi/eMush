<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\PlaceTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PatrolShipIsNotInARoomValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof PatrolShipIsNotInARoom) {
            throw new UnexpectedTypeException($constraint, PatrolShipIsNotInARoom::class);
        }

        $actionTarget = $value->getTarget();
        if (!$actionTarget instanceof GameEquipment) {
            throw new UnexpectedTypeException($actionTarget, GameEquipment::class);
        }

        if (EquipmentEnum::getPatrolShips()->contains($actionTarget->getName()) && $actionTarget->getPlace()->getType() !== PlaceTypeEnum::ROOM) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
