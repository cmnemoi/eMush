<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PlaceNameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        $action = $value;

        if (!$action instanceof AbstractAction) {
            throw new UnexpectedTypeException($action, AbstractAction::class);
        }

        if (!$constraint instanceof PlaceName) {
            throw new UnexpectedTypeException($constraint, PlaceName::class);
        }

        $player = $action->getPlayer();

        if ($player->isNotInAny($constraint->places)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
