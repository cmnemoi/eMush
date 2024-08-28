<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Communication\UseCase\GetContactablePlayersUseCase;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CanContactACrewmateValidator extends ConstraintValidator
{
    public function __construct(private readonly GetContactablePlayersUseCase $getContactablePlayers) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof CanContactACrewmate) {
            throw new UnexpectedTypeException($constraint, CanContactACrewmate::class);
        }

        if ($this->getContactablePlayers->execute($value->getPlayer())->isEmpty()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
