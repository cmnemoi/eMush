<?php

declare(strict_types=1);

namespace Mush\Communications\Dto;

use Mush\Communications\Enum\TradeEnum;

final readonly class TradeConfigDto
{
    public function __construct(
        public string $key,
        public TradeEnum $name,
        /** @var string[] */
        public array $tradeOptions = [],
    ) {}
}
