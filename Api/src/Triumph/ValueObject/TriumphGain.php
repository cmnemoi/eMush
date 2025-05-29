<?php

declare(strict_types=1);

namespace Mush\Triumph\ValueObject;

use Mush\Triumph\Enum\TriumphEnum;

final class TriumphGain
{
    public function __construct(
        private readonly TriumphEnum $triumphKey,
        private readonly int $quantity,
        private int $count = 1,
    ) {}

    public function getTriumphKey(): TriumphEnum
    {
        return $this->triumphKey;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function incrementCount(): void
    {
        ++$this->count;
    }

    public function equals(TriumphEnum $triumphKey, int $quantity): bool
    {
        return $this->triumphKey === $triumphKey && $this->quantity === $quantity;
    }

    public static function fromArray(array $gain): self
    {
        return new self($gain['triumphKey'], $gain['quantity'], $gain['count']);
    }

    public function toArray(): array
    {
        return [
            'triumphKey' => $this->triumphKey,
            'quantity' => $this->quantity,
            'count' => $this->count,
        ];
    }
}
