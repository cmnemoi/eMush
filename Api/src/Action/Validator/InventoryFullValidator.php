<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class InventoryFullValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof InventoryFull) {
            throw new UnexpectedTypeException($constraint, InventoryFull::class);
        }

        $player = $value->getPlayer();
        if ($player->getEquipments()->count() >= $player->getPlayerInfo()->getCharacterConfig()->getMaxItemInInventory()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
