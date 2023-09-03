<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class CycleIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::CYCLE_INCREMENT;

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        $statusHolder = $status->getOwner();

        $isStatusHolderAPatrolShip = EquipmentEnum::getPatrolShips()->contains($statusHolder->getName());
        $isStatusHolderInARoom = $statusHolder->getPlace()->getType() === PlaceTypeEnum::ROOM;
        if ($isStatusHolderAPatrolShip && !$isStatusHolderInARoom) {
            return $status;
        }

        return $this->statusService->updateCharge($status, 1, $reasons, $time);
    }
}
