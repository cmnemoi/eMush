<?php

namespace Mush\Status\Service;

use Mush\Status\ChargeStrategies\AbstractChargeStrategy;

interface ChargeStrategyServiceInterface
{
    public function getStrategy(string $actionName): ?AbstractChargeStrategy;
}
