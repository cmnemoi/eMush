<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Entity\Door;
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
        $player = $value->getPlayer();
        $equipment = $value->getParameter();

        if ($equipment !== null) {
            return false;
        }

        $alert = $this->alertService->findByNameAndDaedalus(AlertEnum::FIRES, $player->getDaedalus());
        if ($alert === null) {
            throw new \LogicException('There should be an alert entity found for this Daedalus');
        }

        return $this->alertService->getAlertFireElement($alert, $player->getPlace())->getPlayer() !== null;
    }

    private function isEquipmentAlertReported(AbstractAction $value): bool
    {
        $player = $value->getPlayer();
        $equipment = $value->getParameter();

        if ($equipment === null) {
            return false;
        }

        if ($equipment instanceof Door) {
            $alert = $this->alertService->findByNameAndDaedalus(AlertEnum::BROKEN_DOORS, $player->getDaedalus());
        } elseif ($equipment instanceof GameEquipment) {
            $alert = $this->alertService->findByNameAndDaedalus(AlertEnum::BROKEN_EQUIPMENTS, $player->getDaedalus());
        } else {
            throw new UnexpectedTypeException($equipment, GameEquipment::class);
        }

        if ($alert === null) {
            throw new \LogicException('There should be an alert entity found for this Daedalus');
        }

        return $this->alertService->getAlertEquipmentElement($alert, $equipment)->getPlayer() !== null;
    }
}
