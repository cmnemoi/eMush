<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\ConstraintValidator;

final class NoPariahOnBoardValidator extends ConstraintValidator
{
    public function validate($value, $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof NoPariahOnBoard) {
            throw new UnexpectedTypeException($constraint, NoPariahOnBoard::class);
        }

        $action = $value;
        $daedalus = $action->getPlayer()->getDaedalus();

        if ($daedalus->hasAPariah()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
