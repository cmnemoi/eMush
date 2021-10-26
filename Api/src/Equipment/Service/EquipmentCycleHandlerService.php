<?php

namespace Mush\Equipment\Service;

use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Game\CycleHandler\AbstractCycleHandler;

class EquipmentCycleHandlerService implements EquipmentCycleHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractCycleHandler $cycleHandler): void
    {
        $this->strategies[$cycleHandler->getName()] = $cycleHandler;
    }

    public function getEquipmentCycleHandler(EquipmentMechanic $mechanic): ?AbstractCycleHandler
    {
        if ($mechanic instanceof Ration) {
            dump(isset($this->strategies[$mechanic->getMechanic()]));
        }
        if (!isset($this->strategies[$mechanic->getMechanic()])) {
            return null;
        }

        return $this->strategies[$mechanic->getMechanic()];
    }
}
