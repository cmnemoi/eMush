<?php

declare(strict_types=1);

namespace Mush\Communications\Dto;

final class TradeConfigDto
{
    public string $key;
    public string $name;
    public array $tradeOptions = [];

    public static function fromJson(array $data): self
    {
        $dto = new self();
        $dto->key = $data['key'];
        $dto->name = $data['name'];
        $dto->tradeOptions = $data['tradeOptions'] ?? [];

        return $dto;
    }
}
