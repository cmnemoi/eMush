<?php

declare(strict_types=1);

namespace Mush\Game\Service;

final class GetRandomIntegerService implements GetRandomIntegerServiceInterface
{
    public function execute(int $min, int $max): int
    {
        return random_int($min, $max);
    }
}