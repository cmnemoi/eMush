<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class GrenadeInhibitValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof GrenadeInhibit) {
            throw new UnexpectedTypeException($constraint, GrenadeInhibit::class);
        }

        $player = $value->getPlayer();
        $neron = $player->getDaedalus()->getNeron();

        if ($neron->isInhibited() && $player->isHuman()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
