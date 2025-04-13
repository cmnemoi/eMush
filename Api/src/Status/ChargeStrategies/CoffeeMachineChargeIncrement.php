<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\EventEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

final class CoffeeMachineChargeIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::COFFEE_MACHINE_CHARGE_INCREMENT;

    public function execute(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        return $this->apply($status, $reasons, $time);
    }

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        $daedalus = $status->getOwner()->getDaedalus();

        $chargeCycle = $daedalus->getNumberOfCyclesPerDay();

        if ($daedalus->getPilgred()->isFinished()) {
            $chargeCycle = ceil($chargeCycle / 4);
        }

        if ($daedalus->hasActiveProject(ProjectName::FISSION_COFFEE_ROASTER)) {
            $chargeCycle = ceil($chargeCycle / 2);
        }

        if (($daedalus->getGameDate()->previousCycle() % $chargeCycle) === 0) {
            return $this->statusService->updateCharge($status, 1, $reasons, $time);
        }

        return $status;
    }

    private function isANewDay(array $reasons): bool
    {
        return \in_array(EventEnum::NEW_DAY, $reasons, true);
    }
}
