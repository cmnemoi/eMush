<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasTitleValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasTitle) {
            throw new UnexpectedTypeException($constraint, HasTitle::class);
        }

        $actionTarget = $value->getTarget();
        $nameTarget = $actionTarget->getLogName();

        if ($actionTarget instanceof GameEquipment) {
            if ($nameTarget === $constraint->terminal) {
                $player = $value->getPlayer();

                if (!$player->hasTitle($constraint->title)) {
                    $this->context->buildViolation($constraint->message)->addViolation();
                }
            }
        }
    }
}
