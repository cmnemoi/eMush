<?php

declare(strict_types=1);

namespace Mush\Project\ValueObject;

use Mush\Project\Exception\PlayerEfficiencyShouldBePositiveException;

final readonly class PlayerEfficiency
{
    public int $min;
    public int $max;

    public function __construct(int $min, int $max)
    {
        if ($min < 0 || $max < 0) {
            throw new PlayerEfficiencyShouldBePositiveException($min, $max);
        }

        $this->min = $min;
        $this->max = $max;
    }
}
