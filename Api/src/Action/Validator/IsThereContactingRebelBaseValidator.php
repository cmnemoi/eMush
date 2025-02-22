<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class IsThereContactingRebelBaseValidator extends ConstraintValidator
{
    public function __construct(private readonly RebelBaseRepositoryInterface $rebelBaseRepository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof IsThereContactingRebelBase) {
            throw new UnexpectedTypeException($constraint, IsThereContactingRebelBase::class);
        }

        $daedalusId = $value->getPlayer()->getDaedalus()->getId();
        if ($this->rebelBaseRepository->hasNoContactingRebelBase($daedalusId)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
