<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AvailableScrapToCollectValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AvailableScrapToCollect) {
            throw new UnexpectedTypeException($constraint, AvailableScrapToCollectValidator::class);
        }

        $collectibleScrap = ItemEnum::getPasiphaeCollectibleScrap();
        $spaceContent = $value->getPlayer()->getDaedalus()->getSpace()->getEquipments();
        $scrapToCollect = $spaceContent->filter(fn (GameEquipment $equipment) => $collectibleScrap->contains($equipment->getName()));

        if ($scrapToCollect->isEmpty()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
