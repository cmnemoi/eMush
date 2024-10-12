<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Place\Enum\RoomEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class MedicalSuppliesOnReachValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        $action = $value;
        if (!$action instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }
        if (!$constraint instanceof MedicalSuppliesOnReach) {
            throw new UnexpectedTypeException($constraint, MedicalSuppliesOnReach::class);
        }

        $player = $action->getPlayer();
        if ($player->isNotIn(RoomEnum::MEDLAB) && $player->doesNotHaveEquipment(ToolItemEnum::MEDIKIT)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
