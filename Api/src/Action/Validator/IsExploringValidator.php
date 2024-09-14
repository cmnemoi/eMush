<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class IsExploringValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof IsExploring) {
            throw new UnexpectedTypeException($constraint, IsExploring::class);
        }

        $action = $value;
        $player = $action->getPlayer();

        if ($player->isExploring() === false) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
