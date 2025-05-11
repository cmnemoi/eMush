<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PlayerMutatedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof PlayerMutated) {
            throw new UnexpectedTypeException($constraint, PlayerMutated::class);
        }

        if (!$value->getPlayer()->hasStatus(PlayerStatusEnum::BERZERK)) {
            return;
        }

        if ($value->isAdminAction()) {
            return;
        }

        $actionName = $value->getActionName();
        $allowedActions = [ActionEnum::MOVE->value, ActionEnum::ATTACK->value, ActionEnum::SABOTAGE->value];

        if (!\in_array($actionName, $allowedActions, true)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
