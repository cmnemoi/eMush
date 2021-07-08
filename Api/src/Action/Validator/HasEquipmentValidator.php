<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasEquipmentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasEquipment) {
            throw new UnexpectedTypeException($constraint, HasEquipment::class);
        }

        $player = $value->getPlayer();

        if ($this->canReachEquipment($player, $constraint->equipment, $constraint->reach) !== $constraint->contains) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function canReachEquipment(Player $player, string $equipmentName, string $reach): bool
    {
        switch ($reach) {
            case ReachEnum::INVENTORY:
                return !$player->getItems()->filter(fn (GameItem $gameItem) => $gameItem->getName() === $equipmentName)->isEmpty();

            case ReachEnum::SHELVE:
                return !$player->getPlace()->getEquipments()->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $equipmentName)->isEmpty();

            case ReachEnum::ROOM:
                return !($player->getPlace()->getEquipments()->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $equipmentName)->isEmpty() &&
                    $player->getItems()->filter(fn (GameItem $gameItem) => $gameItem->getName() === $equipmentName)->isEmpty());
        }

        return true;
    }
}
