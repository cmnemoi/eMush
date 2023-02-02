<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Place\Enum\RoomEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * This class implements a validator for the `areMedicalSuppliesOnReach` constraint.
 */
class AreMedicalSuppliesOnReachValidator extends AbstractActionValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            $errorMessage = "AreMedicalSuppliesOnReachValidator::validate: value must be an instance of AbstractAction";
            $this->logger->error($errorMessage);
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AreMedicalSuppliesOnReach) {
            $errorMessage = "AreMedicalSuppliesOnReachValidator::validate: constraint must be an instance of AreMedicalSuppliesOnReach";
            $this->logger->error($errorMessage,
                [   
                    'daedalus' => $value->getPlayer()->getDaedalus()->getId(),
                    'player' => $value->getPlayer()->getId(),
                ]
            );
            throw new UnexpectedTypeException($constraint, AreMedicalSuppliesOnReach::class);
        }

        $roomName = $value->getPlayer()->getPlace()->getName();
        $IsMedlabRoom = $roomName === RoomEnum::MEDLAB;

        if (!$IsMedlabRoom && !$value->getPlayer()->hasEquipmentByName(ToolItemEnum::MEDIKIT)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
