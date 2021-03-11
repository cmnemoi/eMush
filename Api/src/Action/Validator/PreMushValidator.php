<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Game\Enum\GameStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PreMushValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof PreMush) {
            throw new UnexpectedTypeException($constraint, PreMush::class);
        }

        if ($value->getPlayer()->getDaedalus()->getGameStatus() === GameStatusEnum::STARTING) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
