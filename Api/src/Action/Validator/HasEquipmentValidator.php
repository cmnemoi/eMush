<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
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

        /** @var Player $player */
        $player = match ($constraint->target) {
            HasEquipment::PARAMETER => $value->getParameter(),
            HasEquipment::PLAYER => $value->getPlayer(),
            default => throw new LogicException('unsupported target'),
        };

        if (
            $this->canReachEquipments(
                $player,
                $constraint->equipments,
                $constraint->reach,
                $constraint->checkIfOperational,
                $constraint->all,
            ) !== $constraint->contains
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function canReachEquipments(
        Player $player,
        array $equipmentsName,
        string $reach,
        bool $checkIfOperational,
        bool $all
    ): bool {
        foreach ($equipmentsName as $equipmentName) {
            if ($all && !$this->canReachEquipment($player, $equipmentName, $reach, $checkIfOperational)) {
                return false;
            } elseif (!$all && $this->canReachEquipment($player, $equipmentName, $reach, $checkIfOperational)) {
                return true;
            }
        }

        if ($all) {
            return true;
        } else {
            return false;
        }
    }

    private function canReachEquipment(
        Player $player,
        string $equipmentName,
        string $reach,
        bool $checkIfOperational
    ): bool {
        switch ($reach) {
            case ReachEnum::INVENTORY:
                $equipments = $player->getEquipments()->filter(fn (GameItem $gameItem) => $gameItem->getName() === $equipmentName);
                if ($checkIfOperational) {
                    return !$equipments->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty();
                }

                return !$equipments->isEmpty();

            case ReachEnum::SHELVE:
                $equipments = $player->getPlace()->getEquipments()->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $equipmentName);
                if ($checkIfOperational) {
                    return !$equipments->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty();
                }

                return !$equipments->isEmpty();

            case ReachEnum::ROOM:
                $shelfEquipments = $player->getPlace()->getEquipments()->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $equipmentName);
                $playerEquipments = $player->getEquipments()->filter(fn (GameItem $gameItem) => $gameItem->getName() === $equipmentName);
                if ($checkIfOperational) {
                    return !($playerEquipments->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty()
                    && $shelfEquipments->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty());
                }

                return !($shelfEquipments->isEmpty() && $playerEquipments->isEmpty());
        }

        return true;
    }
}
