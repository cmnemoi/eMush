<?php

declare(strict_types=1);

namespace Mush\Equipment\ValueObject;

final readonly class DamageSpread
{
    public int $min;
    public int $max;

    public function __construct(int $min, int $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public static function fromArray(array $damageSpread): self
    {
        return new self($damageSpread[0], $damageSpread[1]);
    }

    public function equals(self $damageSpread): bool
    {
        return $this->min === $damageSpread->min && $this->max === $damageSpread->max;
    }
}
