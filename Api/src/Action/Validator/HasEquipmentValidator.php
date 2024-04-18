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

        /** @var Player $player */
        $player = match ($constraint->target) {
            HasEquipment::PARAMETER => $value->getTarget(),
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
            }
            if (!$all && $this->canReachEquipment($player, $equipmentName, $reach, $checkIfOperational)) {
                return true;
            }
        }

        if ($all) {
            return true;
        }

        return false;
    }

    private function canReachEquipment(
        Player $player,
        string $equipmentName,
        string $reach,
        bool $checkIfOperational
    ): bool {
        switch ($reach) {
            case ReachEnum::INVENTORY:
                return $this->canReachEquipmentInInventory($player, $equipmentName, $checkIfOperational);

            case ReachEnum::SHELVE:
                return $this->canReachEquipmentInShelf($player, $equipmentName, $checkIfOperational);

            case ReachEnum::ROOM:
                return $this->canReachEquipmentInRoom($player, $equipmentName, $checkIfOperational);

            case ReachEnum::DAEDALUS:
                return $this->canReachEquipmentInDaedalus($player, $equipmentName, $checkIfOperational);
        }

        return true;
    }

    private function canReachEquipmentInInventory(Player $player, string $equipmentName, bool $checkIfOperational): bool
    {
        $equipments = $player->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->getName() === $equipmentName);
        if ($checkIfOperational) {
            return !$equipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty();
        }

        return !$equipments->isEmpty();
    }

    private function canReachEquipmentInShelf(Player $player, string $equipmentName, bool $checkIfOperational): bool
    {
        $equipments = $player->getPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $equipmentName);
        if ($checkIfOperational) {
            return !$equipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty();
        }

        return !$equipments->isEmpty();
    }

    private function canReachEquipmentInRoom(Player $player, string $equipmentName, bool $checkIfOperational): bool
    {
        $shelfEquipments = $player->getPlace()->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $equipmentName);
        $playerEquipments = $player->getEquipments()->filter(static fn (GameItem $gameItem) => $gameItem->getName() === $equipmentName);

        if ($checkIfOperational) {
            return !($playerEquipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty()
            && $shelfEquipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty());
        }

        return !($shelfEquipments->isEmpty() && $playerEquipments->isEmpty());
    }

    private function canReachEquipmentInDaedalus(Player $player, string $equipmentName, bool $checkIfOperational): bool
    {
        $equipments = $this->gameEquipmentService->findByNameAndDaedalus($equipmentName, $player->getDaedalus());
        if ($checkIfOperational) {
            return !$equipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty();
        }

        return !$equipments->isEmpty();
    }
}
