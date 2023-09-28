<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReachValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Reach) {
            throw new UnexpectedTypeException($constraint, Reach::class);
        }

        $actionSupport = $value->getSupport();
        $player = $value->getPlayer();

        if ($actionSupport instanceof GameEquipment) {
            $canReach = $this->canReachGameEquipment($player, $actionSupport, $constraint->reach);
        } elseif ($actionSupport instanceof Player) {
            $canReach = $this->canReachPlayer($player, $actionSupport, $constraint->reach);
        } elseif ($actionSupport instanceof Hunter) {
            $canReach = $this->canReachHunter($player, $constraint->reach);
        } else {
            throw new LogicException('invalid parameter type');
        }

        if (!$canReach) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function canReachPlayer(Player $player, Player $actionSupport, string $reach): bool
    {
        if ($reach !== ReachEnum::ROOM) {
            throw new LogicException('invalid reach for player');
        }

        if ($actionSupport === $player
            || $actionSupport->getPlace() !== $player->getPlace()
            || !$actionSupport->isAlive()
        ) {
            return false;
        }

        return true;
    }

    private function canReachGameEquipment(Player $player, GameEquipment $actionSupport, string $reach): bool
    {
        switch ($reach) {
            case ReachEnum::INVENTORY:
                if (!$actionSupport instanceof GameItem) {
                    throw new UnexpectedTypeException($actionSupport, GameItem::class);
                }

                if (!$player->getEquipments()->contains($actionSupport)) {
                    return false;
                }
                break;
            case ReachEnum::SHELVE:
                if (!$actionSupport instanceof GameItem) {
                    throw new UnexpectedTypeException($actionSupport, GameItem::class);
                }

                if (!$player->getPlace()->getEquipments()->contains($actionSupport)) {
                    return false;
                }
                break;

            case ReachEnum::ROOM:
                if (!$player->canReachEquipment($actionSupport)) {
                    return false;
                }
                break;

            case ReachEnum::SPACE_BATTLE:
                if (!$player->canSeeSpaceBattle() || !$player->canReachEquipment($actionSupport)) {
                    return false;
                }
                break;
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
