<?php

declare(strict_types=1);

namespace Mush\Disease\Dto;

final readonly class DiseaseCauseConfigDto
{
    public function __construct(
        public string $key,
        public string $name,
        public array $diseases
    ) {}
}
