<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Place\Enum\RoomEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class CanGoToIcarusBayValidator extends ConstraintValidator
{
    private const MAX_NUMBER_OF_PLAYERS_IN_ICARUS_BAY = 4;

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }
        if (!$constraint instanceof CanGoToIcarusBay) {
            throw new UnexpectedTypeException($constraint, CanGoToIcarusBay::class);
        }

        $player = $value->getPlayer();
        $icarusBay = $player->getDaedalus()->getPlaceByName(RoomEnum::ICARUS_BAY);
        if (!$icarusBay) {
            throw new \Exception('Daedalus should have a place named Icarus bay');
        }

        $numberOfPlayersInIcarusBay = $icarusBay->getPlayers()->getPlayerAlive()->count();

        if ($numberOfPlayersInIcarusBay >= self::MAX_NUMBER_OF_PLAYERS_IN_ICARUS_BAY && $player->getPlace() !== $icarusBay) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
