<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Repository;

use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Repository\TradeOptionRepositoryInterface;

final class InMemoryTradeOptionRepository implements TradeOptionRepositoryInterface
{
    private array $tradeOptions = [];

    public function findByIdOrThrow(int $id): TradeOption
    {
        return $this->tradeOptions[$id];
    }

    public function save(TradeOption $tradeOption): void
    {
        $this->setupId($tradeOption);
        $this->tradeOptions[$tradeOption->getId()] = $tradeOption;
    }

    private function setupId(TradeOption $tradeOption): void
    {
        $reflectionProperty = new \ReflectionProperty(TradeOption::class, 'id');
        $reflectionProperty->setValue($tradeOption, (int) hash('crc32b', serialize($tradeOption)));
    }
}
