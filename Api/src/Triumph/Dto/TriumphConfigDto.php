<?php

declare(strict_types=1);

namespace Mush\Triumph\Dto;

use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphVisibility;

final readonly class TriumphConfigDto
{
    public function __construct(
        public string $key,
        public TriumphEnum $name,
        public TriumphScope $scope,
        public string $targetedEvent,
        public int $quantity,
        public bool $hasComputeStrategy = false,
        public TriumphVisibility $visibility = TriumphVisibility::PRIVATE,
        public string $target = '',
        public int $regressiveFactor = 0,
        public string $computeStrategy = '',
        public array $applicationStrategies = [],
    ) {}
}
