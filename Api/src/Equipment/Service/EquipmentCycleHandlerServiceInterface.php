<?php

namespace Mush\Equipment\Service;

use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Game\CycleHandler\AbstractCycleHandler;

interface EquipmentCycleHandlerServiceInterface
{
    public function getEquipmentCycleHandler(EquipmentMechanic $mechanic): ?AbstractCycleHandler;
}
