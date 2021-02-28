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
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        $player = $value->getPlayer();
        if ($player->getItems()->count() >= $player->getDaedalus()->getGameConfig()->getMaxItemInInventory()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
