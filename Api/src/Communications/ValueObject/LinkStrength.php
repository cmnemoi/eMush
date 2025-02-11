<?php

declare(strict_types=1);

namespace Mush\Communications\ValueObject;

final readonly class LinkStrength
{
    public int $value;

    private function __construct(int $value)
    {
        $this->value = max(0, min(100, $value));
    }

    public static function create(int $value): self
    {
        return new self($value);
    }

    public function increase(int $delta): self
    {
        return new self($this->value + $delta);
    }
}
