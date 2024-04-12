<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AreShowersDismantledValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AreShowersDismantled) {
            throw new UnexpectedTypeException($constraint, AreShowersDismantled::class);
        }

        $rooms = $value->getPlayer()->getDaedalus()->getRooms();

        /** @var Place $room */
        $numberOfShowers = $rooms->filter(static function ($room) {
            return $room->hasEquipmentByName(EquipmentEnum::SHOWER)
                || $room->hasEquipmentByName(EquipmentEnum::THALASSO);
        })->count();

        if ($numberOfShowers > 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
