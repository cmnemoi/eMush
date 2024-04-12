<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class AvailableScrapToCollectValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AvailableScrapToCollect) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $spaceContent = $value->getPlayer()->getDaedalus()->getSpace()->getEquipments();
        if ($spaceContent->isEmpty()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
