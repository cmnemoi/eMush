<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
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

        $player = $value->getPlayer();

        if (($equipment = $value->getParameter()) == null) {
            $alertName = AlertEnum::FIRES;
        } elseif ($equipment instanceof Door) {
            $alertName = AlertEnum::BROKEN_DOORS;
        } elseif ($equipment instanceof GameEquipment) {
            $alertName = AlertEnum::BROKEN_EQUIPMENTS;
        } else {
            throw new \LogicException('no matching alert for this parameter');
        }

        $alert = $this->alertService->findByNameAndDaedalus($alertName, $player->getDaedalus());
        if ($alert === null) {
            throw new \LogicException('There should be an alert entity found for this Daedalus');
        }

        if ($alertName === AlertEnum::FIRES && $this->isFireReported($alert, $player->getPlace()) ||
            ($equipment !== null && $this->isEquipmentReported($alert, $equipment))
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function isFireReported(Alert $alert, Place $place): bool
    {
        return $alert->getAlertElements()->filter(fn (AlertElement $element) => $element->getPlace() === $place)->first()->getPlayer() !== null;
    }

    private function isEquipmentReported(Alert $alert, GameEquipment $equipment): bool
    {
        return $alert->getAlertElements()->filter(fn (AlertElement $element) => $element->getEquipment() === $equipment)->first()->getPlayer() !== null;
    }
}
