<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Mush\Communications\Entity\Trade;

interface TradeRepositoryInterface
{
    /**
     * @return Trade[]
     */
    public function findAllByDaedalusId(int $daedalusId): array;

    public function findByTransportId(int $transportId): ?Trade;

    public function isThereAvailableTrade(int $daedalusId): bool;

    public function save(Trade $trade): void;

    public function deleteByTradeOptionId(int $tradeOptionId): void;

    public function deleteByTransportId(int $transportId): void;
}
