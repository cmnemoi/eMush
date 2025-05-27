<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\EventEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

final class CoffeeMachineChargeIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::COFFEE_MACHINE_CHARGE_INCREMENT;

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        $daedalus = $status->getOwner()->getDaedalus();

        if (
            $daedalus->pilgredIsNotFinished()
            && $this->isNotANewDay($reasons)
            && $daedalus->fissionCoffeeRoasterNotReady()
        ) {
            return $status;
        }

        return $this->statusService->updateCharge($status, 1, $reasons, $time);
    }

    private function isNotANewDay(array $reasons): bool
    {
        return !\in_array(EventEnum::NEW_DAY, $reasons, true);
    }
}
