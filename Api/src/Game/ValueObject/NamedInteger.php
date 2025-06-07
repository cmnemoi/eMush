<?php

declare(strict_types=1);

namespace Mush\Game\ValueObject;

final readonly class NamedInteger
{
    public function __construct(
        public string $name,
        public int $value,
    ) {}
}
