<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Hunter\Entity\Hunter;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReachValidator extends ConstraintValidator
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
            $astroTerminal = $this->gameEquipmentService->findByNameAndDaedalus(EquipmentEnum::ASTRO_TERMINAL, $player->getDaedalus())->first();
            if (!$astroTerminal) {
                throw new LogicException('astro terminal not found');
            }
            $canReach = $this->canReachGameEquipment($player, $astroTerminal, $constraint->reach);
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

        if ($actionTarget === $player
            || $actionTarget->getPlace() !== $player->getPlace()
            || !$actionTarget->isAlive()
        ) {
            return false;
        }

        return true;
    }

    private function canReachGameEquipment(Player $player, GameEquipment $actionTarget, string $reach): bool
    {
        switch ($reach) {
            case ReachEnum::INVENTORY:
                return $this->canReachItemInInventory($player, $actionTarget);
                break;
            case ReachEnum::SHELVE:
                return $this->canReachItemInShelf($player, $actionTarget);
                break;
            case ReachEnum::ROOM:
                if (!$player->canReachEquipment($actionTarget)) {
                    return false;
                }
                break;
            case ReachEnum::SPACE_BATTLE:
                return $this->canReachSpaceBattle($player, $actionTarget);
                break;
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
}
