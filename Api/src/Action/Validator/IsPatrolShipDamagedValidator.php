<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class IsPatrolShipDamagedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof IsPatrolShipDamaged) {
            throw new UnexpectedTypeException($constraint, IsPatrolShipDamaged::class);
        }

        /** @var GameEquipment $patrolShip */
        $patrolShip = $value->getTarget();
        /** @var ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);

        if ($patrolShipArmor->getCharge() === $patrolShipArmor->getThreshold()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
