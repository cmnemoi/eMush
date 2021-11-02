<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EmptyBedInRoomValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof EmptyBedInRoom) {
            throw new UnexpectedTypeException($constraint, EmptyBedInRoom::class);
        }

        $player = $value->getPlayer();

        $bedsInRoom = $player->getPlace()->getEquipments()->filter(fn (GameEquipment $gameEquipment) => in_array($gameEquipment->getName(), EquipmentEnum::getBeds()));

        if ($bedsInRoom->filter(fn (GameEquipment $gameEquipment) => !$gameEquipment->hasTargetingStatus(PlayerStatusEnum::LYING_DOWN))->isEmpty()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
