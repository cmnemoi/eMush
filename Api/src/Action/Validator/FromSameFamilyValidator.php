<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FromSameFamilyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof FromSameFamily) {
            throw new UnexpectedTypeException($constraint, FromSameFamily::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof Player) {
            throw new UnexpectedTypeException($parameter, Player::class);
        }

        $targetPlayer = $parameter->getCharacterConfig()->getName();
        $player = $value->getPlayer()->getCharacterConfig()->getName();

        if (CharacterEnum::isFromRinaldoFamily($player) && CharacterEnum::isFromRinaldoFamily($targetPlayer)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
