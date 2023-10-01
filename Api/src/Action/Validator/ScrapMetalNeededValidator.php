<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Enum\PlaceTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ScrapMetalNeededValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ScrapMetalNeeded) {
            throw new UnexpectedTypeException($constraint, ScrapMetalNeeded::class);
        }

        /** @var GameEquipment $target */
        $target = $value->getTarget();
        if (!in_array($target->getName(), $constraint->targetNames)) {
            return;
        }

        $scrapMetalInRoom = $target->getPlace()->hasEquipmentByName(ItemEnum::METAL_SCRAPS) || $value->getPlayer()->hasEquipmentByName(ItemEnum::METAL_SCRAPS);

        if (in_array(PlaceTypeEnum::ROOM, $constraint->roomTypes)) {
            if ($target->getPlace()->isARoom() && !$scrapMetalInRoom) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
