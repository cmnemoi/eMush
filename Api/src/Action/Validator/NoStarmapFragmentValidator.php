<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Enum\ItemEnum;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NoStarmapFragmentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof NoStarmapFragment) {
            throw new UnexpectedTypeException($constraint, NoStarmapFragment::class);
        }

        $place = $value->getPlayer()->getPlace();

        if ($place->doesNotHaveVisibleEquipmentByName(ItemEnum::STARMAP_FRAGMENT)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
