<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class PatrolShipChargeIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::PATROL_SHIP_CHARGE_INCREMENT;

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $status->getOwner();
        // If the patrol ship is in battle, do not increment the charge
        if ($patrolShip->isInSpaceBattle()) {
            return $status;
        }

        return $this->statusService->updateCharge($status, 1, $reasons, $time);
    }
}
