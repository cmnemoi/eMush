<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasEquipmentValidator extends ConstraintValidator
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(GameEquipmentServiceInterface $gameEquipmentService)
    {
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasEquipment) {
            throw new UnexpectedTypeException($constraint, HasEquipment::class);
        }

        $player = match ($constraint->target) {
            HasEquipment::PARAMETER => $value->playerTarget(),
            HasEquipment::PLAYER => $value->getPlayer(),
            default => throw new LogicException('unsupported target'),
        };

        if ($this->canReachEquipments($player, $constraint) !== $constraint->contains) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function canReachEquipments(Player $player, HasEquipment $constraint): bool
    {
        $equipmentsName = $constraint->equipments;
        $all = $constraint->all;

        foreach ($equipmentsName as $equipmentName) {
            if ($all && !$this->canReachEquipment($player, $equipmentName, $constraint)) {
                return false;
            }
            if (!$all && $this->canReachEquipment($player, $equipmentName, $constraint)) {
                return true;
            }
        }

        if ($all) {
            return true;
        }

        return false;
    }

    private function canReachEquipment(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        $reach = $constraint->reach;

        switch ($reach) {
            case ReachEnum::INVENTORY:
                return $this->canReachEquipmentInInventory($player, $equipmentName, $constraint);

            case ReachEnum::SHELVE:
                return $this->canReachEquipmentInShelf($player, $equipmentName, $constraint);

            case ReachEnum::ROOM:
                return $this->canReachEquipmentInRoom($player, $equipmentName, $constraint);

            case ReachEnum::DAEDALUS:
                return $this->canReachEquipmentInDaedalus($player, $equipmentName, $constraint);
        }

        return true;
    }

    private function canReachEquipmentInInventory(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        $checkIfOperational = $constraint->checkIfOperational;
        $number = $constraint->number;

        $equipments = $player->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->getName() === $equipmentName);
        if ($checkIfOperational) {
            return !$equipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty();
        }

        return $equipments->count() >= $number;
    }

    private function canReachEquipmentInShelf(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        $checkIfOperational = $constraint->checkIfOperational;
        $number = $constraint->number;

        $equipments = $player->getPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $equipmentName);
        if ($checkIfOperational) {
            return !$equipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty();
        }

        return $equipments->count() >= $number;
    }

    private function canReachEquipmentInRoom(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        $checkIfOperational = $constraint->checkIfOperational;
        $number = $constraint->number;

        $shelfEquipments = $player->getPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $equipmentName);
        $playerEquipments = $player->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->getName() === $equipmentName);

        if ($checkIfOperational) {
            return !($playerEquipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty()
            && $shelfEquipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty());
        }

        $shelfEquipmentsCount = $shelfEquipments->count();
        $playerEquipmentsCount = $playerEquipments->count();

        return $playerEquipmentsCount + $shelfEquipmentsCount >= $number;
    }

    private function canReachEquipmentInDaedalus(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        $checkIfOperational = $constraint->checkIfOperational;
        $number = $constraint->number;

        $equipments = $this->gameEquipmentService->findByNameAndDaedalus($equipmentName, $player->getDaedalus());
        if ($checkIfOperational) {
            return !$equipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty();
        }

        return $equipments->count() >= $number;
    }
}
