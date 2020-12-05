<?php

namespace Mush\Equipment\Service;

use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Equipment\Entity\EquipmentMechanic;

interface EquipmentCycleHandlerServiceInterface
{
    public function getEquipmentCycleHandler(EquipmentMechanic $mechanic): ?AbstractCycleHandler;
}
