<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Repository;

use Mush\Communications\Entity\Trade;
use Mush\Communications\Repository\TradeRepositoryInterface;

final class InMemoryTradeRepository implements TradeRepositoryInterface
{
    /** @var Trade[] */
    private array $trades = [];

    public function deleteByTradeOptionId(int $tradeOptionId): void
    {
        foreach ($this->trades as $trade) {
            foreach ($trade->getTradeOptions() as $tradeOption) {
                if ($tradeOption->getId() === $tradeOptionId) {
                    unset($this->trades[$trade->getId()]);

                    break;
                }
            }
        }
    }

    public function deleteByTransportId(int $transportId): void
    {
        foreach ($this->trades as $trade) {
            if ($trade->getTransportId() === $transportId) {
                unset($this->trades[$trade->getId()]);
            }
        }
    }

    public function findAllByDaedalusId(int $daedalusId): array
    {
        return array_filter($this->trades, static fn (Trade $trade) => $trade->getTransport()->getDaedalus()->getId() === $daedalusId);
    }

    public function isThereAvailableTrade(int $daedalusId): bool
    {
        return \count(array_filter($this->trades, static fn (Trade $trade) => $trade->getTransport()->getDaedalus()->getId() === $daedalusId)) > 0;
    }

    public function save(Trade $trade): void
    {
        $this->setupId($trade);
        $this->trades[$trade->getId()] = $trade;
    }

    public function findByTransportId(int $transportId): ?Trade
    {
        foreach ($this->trades as $trade) {
            if ($trade->getTransportId() === $transportId) {
                return $trade;
            }
        }

        return null;
    }

    private function setupId(Trade $trade): void
    {
        $reflectionProperty = new \ReflectionProperty(Trade::class, 'id');
        $reflectionProperty->setValue($trade, (int) spl_object_id($trade));
    }
}
