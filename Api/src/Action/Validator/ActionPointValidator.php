<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ActionPointValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ActionPoint) {
            throw new UnexpectedTypeException($constraint, ActionPoint::class);
        }

        if (!$this->canPerformWithActionPoint($value, $value->getPlayer()) ||
            !$this->canPerformWithMovementPoint($value, $value->getPlayer()) ||
            !$this->canPerformWithMoralPoint($value, $value->getPlayer())
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function canPerformWithActionPoint(AbstractAction $action, Player $player): bool
    {
        return $player->getActionPoint() >= $action->getActionPointCost();
    }

    private function canPerformWithMovementPoint(AbstractAction $action, Player $player): bool
    {
        //@TODO: improve movement point calculation (i.e no gravity)
        if ($player->getMovementPoint() === 0 && $player->getActionPoint() > 0) {
            return true;
        }

        return $player->getMovementPoint() >= $action->getMovementPointCost();
    }

    private function canPerformWithMoralPoint(AbstractAction $action, Player $player): bool
    {
        return $player->getMoralPoint() >= $action->getMoralPointCost();
    }
}
