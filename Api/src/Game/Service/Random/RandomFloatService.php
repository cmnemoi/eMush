<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

use Random\Randomizer;

final readonly class RandomFloatService implements RandomFloatServiceInterface
{
    public function generateBetween(float $min, float $max): float
    {
        return (new Randomizer())->getFloat($min, $max);
    }
}
