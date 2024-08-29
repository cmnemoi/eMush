<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Hunter\Entity\Hunter;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ReachValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Reach) {
            throw new UnexpectedTypeException($constraint, Reach::class);
        }

        $actionTarget = $value->getTarget();
        $player = $value->getPlayer();

        if ($actionTarget instanceof GameEquipment) {
            $canReach = $this->canReachGameEquipment($player, $actionTarget, $constraint->reach);
        } elseif ($actionTarget instanceof Player) {
            $canReach = $this->canReachPlayer($player, $actionTarget, $constraint->reach);
        } elseif ($actionTarget instanceof Hunter) {
            $canReach = $this->canReachHunter($player, $constraint->reach);
        } elseif ($actionTarget instanceof Planet) {
            $canReach = $this->canReachPlanet($player);
        } elseif ($actionTarget instanceof Project) {
            $canReach = $this->canReachProject($player);
        } else {
            throw new LogicException('invalid parameter type');
        }

        if (!$canReach) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function canReachPlayer(Player $player, Player $actionTarget, string $reach): bool
    {
        if ($reach !== ReachEnum::ROOM) {
            throw new LogicException('invalid reach for player');
        }

        return !($actionTarget === $player || $actionTarget->isAlive() === false || $actionTarget->getPlace() !== $player->getPlace());
    }

    private function canReachGameEquipment(Player $player, GameEquipment $actionTarget, string $reach): bool
    {
        switch ($reach) {
            case ReachEnum::INVENTORY:
                return $this->canReachItemInInventory($player, $actionTarget);

            case ReachEnum::SHELVE:
                return $this->canReachItemInShelf($player, $actionTarget);

            case ReachEnum::ROOM:
                if (!$player->canReachEquipment($actionTarget)) {
                    return false;
                }

                break;

            case ReachEnum::SPACE_BATTLE:
                return $this->canReachSpaceBattle($player, $actionTarget);
        }

        return true;
    }

    private function canReachItemInInventory(Player $player, GameEquipment $actionTarget): bool
    {
        if (!$actionTarget instanceof GameItem) {
            throw new UnexpectedTypeException($actionTarget, GameItem::class);
        }

        if (!$player->getEquipments()->contains($actionTarget)) {
            return false;
        }

        return true;
    }

    private function canReachItemInShelf(Player $player, GameEquipment $actionTarget): bool
    {
        if (!$actionTarget instanceof GameItem) {
            throw new UnexpectedTypeException($actionTarget, GameItem::class);
        }

        if (!$player->getPlace()->getEquipments()->contains($actionTarget)) {
            return false;
        }

        return true;
    }

    private function canReachSpaceBattle(Player $player, GameEquipment $gameEquipment): bool
    {
        if (!$player->canSeeSpaceBattle() || !$player->canReachEquipment($gameEquipment)) {
            return false;
        }

        return true;
    }

    private function canReachHunter(Player $player, string $reach): bool
    {
        if ($reach !== ReachEnum::SPACE_BATTLE) {
            throw new LogicException('invalid reach for hunter');
        }

        return $player->canSeeSpaceBattle();
    }

    private function canReachPlanet(Player $player): bool
    {
        return $player->isFocusedOnTerminalByName(EquipmentEnum::ASTRO_TERMINAL) && $player->getPlace()->hasEquipmentByName(EquipmentEnum::ASTRO_TERMINAL);
    }

    private function canReachProject(Player $player): bool
    {
        return EquipmentEnum::getProjectTerminals()
            ->filter(static fn (string $name) => $player->isFocusedOnTerminalByName($name) && $player->getPlace()->hasEquipmentByName($name))
            ->count() > 0;
    }
}
