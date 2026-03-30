<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PrivatePropertyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof PrivateProperty) {
            throw new UnexpectedTypeException($constraint, PrivateProperty::class);
        }

        $action = $value;
        $player = $action->getPlayer();
        $item = $action->getActionProvider();

        if ($item instanceof GameEquipment) {
            $status = $item->getStatusByName(EquipmentStatusEnum::PRIVATE_PROPERTY);
            if ($status) {
                $owner = $status->getPlayerTargetOrThrow();
                if ($owner !== null & ($owner->getId() !== $player->getId() && $owner->isAlive())) {
                    $this->context->buildViolation($constraint->message)->addViolation();
                }
            }
        }
    }
}
