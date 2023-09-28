<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsReportedValidator extends ConstraintValidator
{
    private AlertServiceInterface $alertService;

    public function __construct(AlertServiceInterface $alertService)
    {
        $this->alertService = $alertService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof IsReported) {
            throw new UnexpectedTypeException($constraint, IsReported::class);
        }

        if ($this->isFireAlertReported($value) || $this->isEquipmentAlertReported($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function isFireAlertReported(AbstractAction $value): bool
    {
        $equipment = $value->getSupport();
        $place = $value->getPlayer()->getPlace();

        if ($equipment !== null) {
            return false;
        }

        return $this->alertService->isFireReported($place);
    }

    private function isEquipmentAlertReported(AbstractAction $value): bool
    {
        $equipment = $value->getSupport();

        if (!$equipment instanceof GameEquipment) {
            return false;
        }

        return $this->alertService->isEquipmentReported($equipment);
    }
}
