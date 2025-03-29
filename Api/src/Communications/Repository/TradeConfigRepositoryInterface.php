<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Mush\Communications\Entity\TradeConfig;
use Mush\Communications\Enum\TradeEnum;

interface TradeConfigRepositoryInterface
{
    public function findOneByNameAndTransportIdOrThrow(TradeEnum $name, int $transportId): TradeConfig;

    public function save(TradeConfig $tradeConfig): void;
}
