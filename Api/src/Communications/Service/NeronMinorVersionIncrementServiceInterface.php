<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

interface NeronMinorVersionIncrementServiceInterface
{
    public function generateFrom(int $neronMajorVersion): int;
}
