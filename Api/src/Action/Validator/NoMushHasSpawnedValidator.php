<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Player\Entity\Player;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NoMushHasSpawnedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof NoMushHasSpawned) {
            throw new UnexpectedTypeException($constraint, NoMushHasSpawned::class);
        }

        $daedalus = $value->getPlayer()->getDaedalus();
        $alphaMushs = $daedalus->getPlayers()->filter(static fn (Player $player) => $player->isAlphaMush());

        if ($alphaMushs->isEmpty()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
