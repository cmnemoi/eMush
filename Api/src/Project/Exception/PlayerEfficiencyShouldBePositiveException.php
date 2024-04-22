<?php

declare(strict_types=1);

namespace Mush\Project\Exception;

final class PlayerEfficiencyShouldBePositiveException extends \LogicException
{
    public function __construct(int $min, int $max)
    {
        parent::__construct("Efficiency should be positive, but got {$min}%-{$max}% instead.");
    }
}
