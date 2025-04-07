<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\Enum\GameStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AggressivePreMushValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AggressivePreMush) {
            throw new UnexpectedTypeException($constraint, AggressivePreMush::class);
        }

        $action = $value;
        $daedalus = $value->getPlayer()->getDaedalus();

        if ($action->hasTag(ActionTypeEnum::ACTION_AGGRESSIVE->toString())
        && $daedalus->getGameStatus() === GameStatusEnum::STARTING) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
