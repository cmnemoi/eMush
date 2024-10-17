<?php

declare(strict_types=1);

namespace Mush\Daedalus\ValueObject;

final readonly class DaedalusDate
{
    public function __construct(
        public int $day,
        public int $cycle,
    ) {
        if ($day < 1) {
            throw new \InvalidArgumentException('Day must be greater than 0');
        }
        if ($cycle < 1 || $cycle > 8) {
            throw new \InvalidArgumentException('Cycle must be between 1 and 8');
        }
    }
}
