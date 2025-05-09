<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Project\Enum\ProjectName;
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

        $operationalStatus = $actionProvider->getOperationalStatus($actionConfig->getActionName()->value);

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
        $chargeStatus = $actionProvider->getUsedCharge($actionName->value);

        if ($actionProvider instanceof GameEquipment && $actionProvider->getEquipment()->getMechanicByName(EquipmentMechanicEnum::WEAPON)) {
            return ActionImpossibleCauseEnum::UNLOADED_WEAPON;
        }

        $daedalus = $chargeStatus->getOwner()->getDaedalus();

        return match ($chargeStatus->getStrategy()) {
            ChargeStrategyTypeEnum::CYCLE_INCREMENT => ActionImpossibleCauseEnum::CYCLE_LIMIT,
            ChargeStrategyTypeEnum::DAILY_INCREMENT => ActionImpossibleCauseEnum::DAILY_LIMIT,
            ChargeStrategyTypeEnum::COFFEE_MACHINE_CHARGE_INCREMENT => $this->coffeeMachineViolationMessage($daedalus),
            default => $defaultMessage,
        };
    }

    private function coffeeMachineViolationMessage(Daedalus $daedalus): string
    {
        if ($daedalus->getPilgred()->isFinished()) {
            if ($daedalus->getProjectByName(ProjectName::FISSION_COFFEE_ROASTER)->isFinished()) {
                return ActionImpossibleCauseEnum::CYCLE_LIMIT;
            }

            return ActionImpossibleCauseEnum::CYCLE_LIMIT_EVERY_2;
        }
        if ($daedalus->getProjectByName(ProjectName::FISSION_COFFEE_ROASTER)->isFinished()) {
            return ActionImpossibleCauseEnum::CYCLE_LIMIT_EVERY_4;
        }

        return ActionImpossibleCauseEnum::DAILY_LIMIT;
    }
}
