<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\Equipment;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
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

        $parameter = $value->getParameter();
        if (!$parameter instanceof Equipment) {
            throw new UnexpectedTypeException($parameter, Equipment::class);
        }

        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $this->getChargeStatus($value->getActionName(), $parameter);

        if ($chargeStatus !== null && $chargeStatus->getCharge() <= 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function getChargeStatus(string $actionName, StatusHolderInterface $holder): ?ChargeStatus
    {
        $charges = $holder->getStatuses()->filter(function (Status $status) use ($actionName) {
            return $status instanceof ChargeStatus &&
                $status->getDischargeStrategy() === $actionName;
        });

        if ($charges->count() > 0) {
            return $charges->first();
        } elseif ($charges->count() === 0) {
            return null;
        } else {
            throw new LogicException('there should be maximum 1 chargeStatus with this dischargeStrategy on this statusHolder');
        }
    }
}
