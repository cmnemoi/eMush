<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ChargedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Charged) {
            throw new UnexpectedTypeException($constraint, Charged::class);
        }

        $actionTarget = $value->getTarget();
        $actionProvider = $value->getActionProvider();

        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $actionProvider->getUsedCharge($value->getActionConfig()->getActionName());

        if ($chargeStatus !== null && !$chargeStatus->isCharged()) {
            $message = $this->getViolationMessage($chargeStatus, $constraint);
            $this->context->buildViolation($message)->addViolation();
        }
    }

    private function getViolationMessage(ChargeStatus $chargeStatus, Constraint $constraint): string
    {
        $daedalus = $chargeStatus->getOwner()->getDaedalus();

        if ($chargeStatus->getStrategy() === ChargeStrategyTypeEnum::COFFEE_MACHINE_CHARGE_INCREMENT) {
            return $daedalus->isPilgredFinished() ? ActionImpossibleCauseEnum::CYCLE_LIMIT : ActionImpossibleCauseEnum::DAILY_LIMIT;
        }

        return $constraint->message;
    }
}
