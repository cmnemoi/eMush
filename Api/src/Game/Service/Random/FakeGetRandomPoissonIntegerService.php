<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

final class FakeGetRandomPoissonIntegerService implements GetRandomPoissonIntegerServiceInterface
{
    public function __construct(private int $result) {}

    public function execute(float $lambda): int
    {
        return $this->result;
    }
}
