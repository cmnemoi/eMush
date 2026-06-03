<?php

declare(strict_types=1);

namespace Mush\Status\Service;

use Mush\Status\ChargeStrategies\AbstractChargeStrategy;

interface ChargeStrategyServiceInterface
{
    public function getStrategy(string $strategyName): ?AbstractChargeStrategy;
}
