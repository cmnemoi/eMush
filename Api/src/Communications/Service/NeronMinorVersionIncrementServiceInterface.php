<?php

namespace Mush\Communications\Service;

interface NeronMinorVersionIncrementServiceInterface
{
    public function generateFrom(int $neronMajorVersion): int;
}
