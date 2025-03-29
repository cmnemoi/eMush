<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Repository;

use Mush\Communications\Entity\TradeConfig;
use Mush\Communications\Enum\TradeEnum;
use Mush\Communications\Repository\TradeConfigRepositoryInterface;

final class InMemoryTradeConfigRepository implements TradeConfigRepositoryInterface
{
    /**
     * @var TradeConfig[]
     */
    private array $tradeConfigs = [];

    public function findOneByNameAndTransportIdOrThrow(TradeEnum $name, int $transportId): TradeConfig
    {
        foreach ($this->tradeConfigs as $tradeConfig) {
            if ($tradeConfig->getName() === $name) {
                return $tradeConfig;
            }
        }

        throw new \Exception("Trade config {$name->value} not found");
    }

    public function save(TradeConfig $tradeConfig): void
    {
        $this->tradeConfigs[hash('crc32b', serialize($tradeConfig))] = $tradeConfig;
    }
}
