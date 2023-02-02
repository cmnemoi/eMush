<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AreShowersDismantledValidator extends AbstractActionValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof AbstractAction) {
            $errorMessage = "AreShowersDismantledValidator::validate: value must be an instance of AbstractAction";
            $this->logger->error($errorMessage);
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AreShowersDismantled) {
            $errorMessage = "AreShowersDismantledValidator::validate: constraint must be an instance of AreShowersDismantled";
            $this->logger->error($errorMessage,
                [
                    'daedalus' => $value->getPlayer()->getDaedalus()->getId(),
                    'player' => $value->getPlayer()->getId(),
                ]
            );
            throw new UnexpectedTypeException($constraint, AreShowersDismantled::class);
        }

        $rooms = $value->getPlayer()->getDaedalus()->getRooms();

        /** @var $room Place **/
        $numberOfShowers = $rooms->filter(function ($room) {
            return $room->hasEquipmentByName(EquipmentEnum::SHOWER)
                || $room->hasEquipmentByName(EquipmentEnum::THALASSO);
        })->count();

        if ($numberOfShowers > 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
