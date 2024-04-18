<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsSameGenderValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof IsSameGender) {
            throw new UnexpectedTypeException($constraint, IsSameGender::class);
        }

        $actionTarget = $value->getTarget();
        if (!$actionTarget instanceof Player) {
            throw new UnexpectedTypeException($actionTarget, Player::class);
        }

        $targetPlayer = $actionTarget->getPlayerInfo()->getCharacterConfig()->getCharacterName();
        $player = $value->getPlayer()->getPlayerInfo()->getCharacterConfig()->getCharacterName();

        if ($this->isSameGenderCouple($player, $targetPlayer) && !$this->isCoupleWithAndie($player, $targetPlayer)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function isSameGenderCouple(string $player, string $targetPlayer): bool
    {
        $twoMen = CharacterEnum::isMale($player) && CharacterEnum::isMale($targetPlayer);
        $twoWomen = !CharacterEnum::isMale($player) && !CharacterEnum::isMale($targetPlayer);

        return $twoMen || $twoWomen;
    }

    private function isCoupleWithAndie(string $player, string $targetPlayer): bool
    {
        return $player === CharacterEnum::ANDIE || $targetPlayer === CharacterEnum::ANDIE;
    }
}
