<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsActionProviderOperationalValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof IsActionProviderOperational) {
            throw new UnexpectedTypeException($constraint, IsActionProviderOperational::class);
        }

        /** @var ActionProviderInterface $actionProvider */
        $actionProvider = $value->getActionProvider();

        /** @var ActionConfig $actionConfig */
        $actionConfig = $value->getActionConfig();

        $operationalStatus = $actionProvider->getOperationalStatus($actionConfig->getActionName());

        if ($operationalStatus !== ActionProviderOperationalStateEnum::OPERATIONAL) {
            $message = $this->getViolationMessage(
                $operationalStatus,
                $actionProvider,
                $actionConfig->getActionName(),
                $constraint->message
            );
            $this->context->buildViolation($message)->addViolation();
        }
    }

    private function getViolationMessage(
        ActionProviderOperationalStateEnum $operationalState,
        ActionProviderInterface $actionProvider,
        ActionEnum $actionName,
        string $defaultMessage
    ): string {
        return match ($operationalState) {
            ActionProviderOperationalStateEnum::BROKEN => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            ActionProviderOperationalStateEnum::DISCHARGED => $this->getDischargeViolationMessage($actionProvider, $actionName, $defaultMessage),
            default => $defaultMessage,
        };
    }

    private function getDischargeViolationMessage(
        ActionProviderInterface $actionProvider,
        ActionEnum $actionName,
        string $defaultMessage,
    ): string {
        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $actionProvider->getUsedCharge($actionName);

        if ($actionProvider instanceof GameEquipment && $actionProvider->getEquipment()->getMechanicByName(EquipmentMechanicEnum::WEAPON)) {
            return ActionImpossibleCauseEnum::UNLOADED_WEAPON;
        }
        if ($chargeStatus->getStrategy() === ChargeStrategyTypeEnum::CYCLE_INCREMENT) {
            return ActionImpossibleCauseEnum::CYCLE_LIMIT;
        }
        if ($chargeStatus->getStrategy() === ChargeStrategyTypeEnum::DAILY_INCREMENT) {
            return ActionImpossibleCauseEnum::DAILY_LIMIT;
        }
        if ($chargeStatus->getStrategy() === ChargeStrategyTypeEnum::COFFEE_MACHINE_CHARGE_INCREMENT) {
            $daedalus = $chargeStatus->getOwner()->getDaedalus();

            return $daedalus->isPilgredFinished() ? ActionImpossibleCauseEnum::CYCLE_LIMIT : ActionImpossibleCauseEnum::DAILY_LIMIT;
        }

        return $defaultMessage;
    }
}
