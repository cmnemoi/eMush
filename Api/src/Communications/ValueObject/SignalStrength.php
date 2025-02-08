<?php

declare(strict_types=1);

namespace Mush\Communications\ValueObject;

final readonly class SignalStrength
{
    public int $value;

    public function __construct(int $value)
    {
        $this->value = max(0, min(100, $value));
    }

    public function increase(int $delta): self
    {
        $newValue = $this->value + $delta;
        $newValue = min(100, $newValue);

        return new self($newValue);
    }
}
