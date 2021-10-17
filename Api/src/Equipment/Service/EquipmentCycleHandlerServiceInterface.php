<?php

namespace Mush\Equipment\Service;

use Mush\Equipment\Entity\Config\EquipmentMechanic;
use Mush\Game\CycleHandler\AbstractCycleHandler;

interface EquipmentCycleHandlerServiceInterface
{
    public function getEquipmentCycleHandler(EquipmentMechanic $mechanic): ?AbstractCycleHandler;
}
