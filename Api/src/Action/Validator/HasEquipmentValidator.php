<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
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
        return match ($constraint->reach) {
            ReachEnum::INVENTORY => $this->canReachEquipmentInInventory($player, $equipmentName, $constraint),
            ReachEnum::SHELVE => $this->canReachEquipmentInShelf($player, $equipmentName, $constraint),
            ReachEnum::ROOM => $this->canReachEquipmentInRoom($player, $equipmentName, $constraint),
            ReachEnum::DAEDALUS => $this->canReachEquipmentInDaedalus($player, $equipmentName, $constraint),
            ReachEnum::SHELVE_NOT_HIDDEN => $this->canReachEquipmentInShelfNotHidden($player, $equipmentName, $constraint),
            default => throw new LogicException("Unsupported reach {$constraint->reach}"),
        };
    }

    private function canReachEquipmentInInventory(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        if ($constraint->checkIfOperational) {
            return $player->hasAnyOperationalEquipmentsByNames([$equipmentName]);
        }

        return $player->getEquipmentsByNames([$equipmentName])->count() >= $constraint->number;
    }

    private function canReachEquipmentInShelf(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        if ($constraint->checkIfOperational) {
            return $player->getPlace()->hasAnyOperationalEquipmentsByNames([$equipmentName]);
        }

        return $player->getPlace()->getEquipmentsByNames([$equipmentName])->count() >= $constraint->number;
    }

    private function canReachEquipmentInRoom(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        if ($constraint->checkIfOperational) {
            return $player->hasAnyOperationalEquipmentsByNames([$equipmentName])
                || $player->getPlace()->hasAnyOperationalEquipmentsByNames([$equipmentName]);
        }

        $shelfEquipmentsCount = $player->getPlace()->getEquipmentsByNames([$equipmentName])->count();
        $playerEquipmentsCount = $player->getEquipmentsByNames([$equipmentName])->count();

        return $playerEquipmentsCount + $shelfEquipmentsCount >= $constraint->number;
    }

    private function canReachEquipmentInDaedalus(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        $checkIfOperational = $constraint->checkIfOperational;
        $number = $constraint->number;

        $equipments = $this->gameEquipmentService->findByNameAndDaedalus($equipmentName, $player->getDaedalus());
        if ($checkIfOperational) {
            $equipments = $equipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational());
        }

        return $equipments->count() >= $number;
    }

    private function canReachEquipmentInShelfNotHidden(Player $player, string $equipmentName, HasEquipment $constraint): bool
    {
        $checkIfOperational = $constraint->checkIfOperational;
        $number = $constraint->number;

        $equipments = $player
            ->getPlace()
            ->getEquipments()
            ->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $equipmentName)
            ->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->doesNotHaveStatus(EquipmentStatusEnum::HIDDEN));

        if ($checkIfOperational) {
            return !$equipments->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational())->isEmpty();
        }

        return $equipments->count() >= $number;
    }
}
