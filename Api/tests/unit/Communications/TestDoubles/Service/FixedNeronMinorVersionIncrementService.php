<?php

namespace Mush\Tests\unit\Communications\TestDoubles\Service;

use Mush\Communications\Service\NeronMinorVersionIncrementServiceInterface;

final class FixedNeronMinorVersionIncrementService implements NeronMinorVersionIncrementServiceInterface
{
    public function __construct(public int $increment) {}

    public function generateFrom(int $neronMajorVersion): int
    {
        return $this->increment;
    }
}
