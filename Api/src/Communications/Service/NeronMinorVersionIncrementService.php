<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;

final readonly class NeronMinorVersionIncrementService implements NeronMinorVersionIncrementServiceInterface
{
    public function __construct(private GetRandomIntegerServiceInterface $getRandomInteger) {}

    public function generateFrom(int $neronMajorVersion): int
    {
        return (int) round($this->getRandomInteger->execute(1, 100) / $neronMajorVersion);
    }
}
