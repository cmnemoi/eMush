<?php

namespace Mush\Equipment\Service;

use Mush\Game\CycleHandler\AbstractCycleHandler;

class EquipmentCycleHandlerService implements EquipmentCycleHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractCycleHandler $cycleHandler): void
    {
        $this->strategies[$cycleHandler->getName()] = $cycleHandler;
    }

    public function getEquipmentCycleHandler(string $mechanicName): ?AbstractCycleHandler
    {
        if (!isset($this->strategies[$mechanicName])) {
            return null;
        }

        return $this->strategies[$mechanicName];
    }
}
