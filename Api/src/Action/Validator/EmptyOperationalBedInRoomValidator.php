<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EmptyOperationalBedInRoomValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof EmptyOperationalBedInRoom) {
            throw new UnexpectedTypeException($constraint, EmptyOperationalBedInRoom::class);
        }

        $player = $value->getPlayer();

        $bedsInRoom = $player->getPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => \in_array($gameEquipment->getName(), EquipmentEnum::getBeds(), true));
        $bedIsNotOccupied = static fn (GameEquipment $gameEquipment) => !$gameEquipment->hasTargetingStatus(PlayerStatusEnum::LYING_DOWN);
        $bedIsNotBroken = static fn (GameEquipment $gameEquipment) => !$gameEquipment->hasStatus(EquipmentStatusEnum::BROKEN);

        if ($bedsInRoom->filter($bedIsNotOccupied)->filter($bedIsNotBroken)->isEmpty()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
