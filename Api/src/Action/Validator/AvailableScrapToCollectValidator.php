<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
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
            throw new UnexpectedTypeException($constraint, AvailableScrapToCollectValidator::class);
        }

        $spaceContent = $value->getPlayer()->getDaedalus()->getSpace()->getEquipments();
        if ($spaceContent->isEmpty()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
