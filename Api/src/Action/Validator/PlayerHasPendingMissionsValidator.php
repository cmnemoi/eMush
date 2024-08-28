<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\ConstraintValidator;

final class PlayerHasPendingMissionsValidator extends ConstraintValidator
{
    public function validate($value, $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof PlayerHasPendingMissions) {
            throw new UnexpectedTypeException($constraint, PlayerHasPendingMissions::class);
        }

        $player = $value->getPlayer();
        if ($player->hasPendingMissions()) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
