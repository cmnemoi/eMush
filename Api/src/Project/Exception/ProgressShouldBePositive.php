<?php

declare(strict_types=1);

namespace Mush\Project\Exception;

final class ProgressShouldBePositive extends \InvalidArgumentException
{
    public function __construct(int $progress)
    {
        parent::__construct("Progress should be positive, but got {$progress} instead.");
    }
}
