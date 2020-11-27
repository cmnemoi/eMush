<?php

namespace Mush\Status\Service;

use Mush\Status\ChargeStrategies\AbstractChargeStrategy;

class ChargeStrategyService implements ChargeStrategyServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractChargeStrategy $strategy)
    {
        $this->strategies[$strategy->getName()] = $strategy;
    }

    public function getStrategy(string $strategyName): ?AbstractChargeStrategy
    {
        if (!isset($this->strategies[$strategyName])) {
            return null;
        }

        return $this->strategies[$strategyName];
    }
}
