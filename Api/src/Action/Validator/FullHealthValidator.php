<?php


namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Player\Enum\ModifierTargetEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MaxHealthValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof MaxHealth) {
            throw new UnexpectedTypeException($constraint, Reach::class);
        }

        $player = match ($constraint->target) {
            FullHealth::PARAMETER => $value->getParameter(),
            FullHealth::PLAYER => $value->getPlayer()
        };


        if($player->getHealthPoint() === $player->getCharacterConfig()->getGameConfig()->getMaxHealthPoint()){
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
