<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ActionProviderIsInPlayerInventoryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ActionProviderIsInPlayerInventory) {
            throw new UnexpectedTypeException($constraint, ActionProviderIsInPlayerInventory::class);
        }

        $actionProvider = $value->getActionProvider();
        if (!$actionProvider instanceof GameItem) {
            throw new UnexpectedTypeException($actionProvider, GameItem::class);
        }

        $player = $value->getPlayer();

        if ($player->hasEquipmentByName($actionProvider->getName()) === false) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
