<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Communications\Repository\LinkWithSolRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class LinkWithSolConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly LinkWithSolRepository $linkWithSolRepository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof LinkWithSolConstraint) {
            throw new UnexpectedTypeException($constraint, LinkWithSolConstraint::class);
        }

        $daedalusId = $value->getPlayer()->getDaedalus()->getId();
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($daedalusId);

        if ($constraint->shouldBeEstablished !== $linkWithSol->isEstablished()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
