<?php

namespace Mush\Equipment\Service;

use Mush\Game\CycleHandler\AbstractCycleHandler;

interface EquipmentCycleHandlerServiceInterface
{
    public function getEquipmentCycleHandler(string $mechanicName): ?AbstractCycleHandler;
}
