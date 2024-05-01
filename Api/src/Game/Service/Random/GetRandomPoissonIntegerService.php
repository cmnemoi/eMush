<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

final class GetRandomPoissonIntegerService implements GetRandomPoissonIntegerServiceInterface
{   
    private function __construct(private GetRandomIntegerServiceInterface $random) {}

    public function execute(float $lambda): int
    {
        if ($lambda < 0) {
            throw new \Exception("poissonRandom: lambda ({$lambda}) must be positive");
        }

        $L = exp(-$lambda);
        $k = 0;
        $p = 1;

        do {
            ++$k;
            $p *= $this->random->execute(1, 100) / 100;
        } while ($p > $L);

        return $k - 1;
    }
}