<?php

declare(strict_types=1);

namespace Mush\Triumph\ValueObject;

use Mush\Triumph\Enum\TriumphEnum;

final class TriumphGain
{
    public function __construct(
        private readonly TriumphEnum $triumphKey,
        private readonly int $value,
        private int $count = 1,
        private ?bool $isMush = null,
    ) {}

    public function getTriumphKey(): TriumphEnum
    {
        return $this->triumphKey;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getValueAsString(): string
    {
        return $this->value > 0 ? "+{$this->value}" : "{$this->value}";
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
        return $this->triumphKey === $triumphKey && $this->value === $quantity;
    }

    public function toEmoteCode(): string
    {
        if ($this->isIsMushSet()) {
            return $this->triumphKey->toEmoteCode();
        }

        return $this->isMush ? ':triumph_mush:' : ':triumph:';
    }

    public static function fromArray(array $gain): self
    {
        return new self($gain['triumphKey'], $gain['value'], $gain['count'], $gain['isMush'] ?? null);
    }

    public function toArray(): array
    {
        return [
            'triumphKey' => $this->triumphKey,
            'value' => $this->value,
            'count' => $this->count,
            'isMush' => $this->isMush,
        ];
    }

    public function setIsMush(bool $isMush): void
    {
        $this->isMush = $isMush;
    }

    private function isIsMushSet(): bool
    {
        return $this->isMush === null;
    } 
}
