<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

interface RandomFloatServiceInterface
{
    /**
     * @return float between min and max (exclusive)
     */
    public function generateBetween(float $min, float $max): float;
}
