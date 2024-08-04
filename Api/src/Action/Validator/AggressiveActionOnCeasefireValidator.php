<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class AggressiveActionOnCeasefireValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AggressiveActionOnCeasefire) {
            throw new UnexpectedTypeException($constraint, AggressiveActionOnCeasefire::class);
        }

        $action = $value;
        $playerRoom = $action->getPlayer()->getPlace();

        if ($action->hasTag(ActionTypeEnum::ACTION_AGGRESSIVE->value)
            && $playerRoom->hasStatus(PlaceStatusEnum::CEASEFIRE->toString())
        ) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
