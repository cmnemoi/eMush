<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ForbiddenLoveValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ForbiddenLove) {
            throw new UnexpectedTypeException($constraint, ForbiddenLove::class);
        }

        $actionSupport = $value->getSupport();
        if (!$actionSupport instanceof Player) {
            throw new UnexpectedTypeException($actionSupport, Player::class);
        }

        $targetPlayer = $actionSupport->getName();
        $player = $value->getPlayer()->getName();

        if (CharacterEnum::isFromRinaldoFamily($player) && CharacterEnum::isFromRinaldoFamily($targetPlayer)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
