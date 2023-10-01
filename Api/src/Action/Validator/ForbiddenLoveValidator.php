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

        $actionTarget = $value->getTarget();
        if (!$actionTarget instanceof Player) {
            throw new UnexpectedTypeException($actionTarget, Player::class);
        }

        $targetPlayer = $actionTarget->getName();
        $player = $value->getPlayer()->getName();

        if (CharacterEnum::isFromRinaldoFamily($player) && CharacterEnum::isFromRinaldoFamily($targetPlayer)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
