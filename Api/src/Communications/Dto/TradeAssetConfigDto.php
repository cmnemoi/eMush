<?php

declare(strict_types=1);

namespace Mush\Communications\Dto;

use Mush\Communications\Enum\TradeAssetEnum;

final readonly class TradeAssetConfigDto
{
    public function __construct(
        public string $name,
        public TradeAssetEnum $type,
        public int $minQuantity,
        public int $maxQuantity,
        public string $assetName = '',
    ) {}
}
